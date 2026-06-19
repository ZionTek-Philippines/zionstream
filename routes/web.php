<?php

use App\Models\Channel;
use App\Models\Product;
use App\Models\Stream;
use App\Models\StreamProduct;
use App\Models\User;
use App\Services\Agora\AgoraService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;
use Laravel\Socialite\Facades\Socialite;


// ─── Guest Auth ───────────────────────────────────────────────────────────────

Route::get('/app/auth', fn() => view('app.auth.login'))->name('app.auth.login');

Route::post('/app/auth', function (Request $request) {
    $credentials = $request->validate([
        'email'    => ['required', 'email'],
        'password' => ['required'],
    ]);

    if (Auth::attempt($credentials, $request->boolean('remember'))) {
        $request->session()->regenerate();
        return redirect()->intended(route('app.landing'))->with('login_role', Auth::user()->getRoleNames()->first());
    }

    return back()
        ->withErrors(['email' => 'These credentials do not match our records.'])
        ->onlyInput('email');
})->name('app.auth.post');

Route::get('/app/auth/facebook', function () {
    return Socialite::driver('facebook')->redirect();
})->name('app.auth.facebook');

Route::get('/app/auth/facebook/callback', function () {
    try {
        $fbUser = Socialite::driver('facebook')->user();
    } catch (\Exception $e) {
        return redirect()->route('app.auth.login')
            ->with('error', 'Facebook login failed. Please try again.');
    }

    $user = User::where('facebook_id', $fbUser->getId())->first()
        ?? User::where('email', $fbUser->getEmail())->first();

    if ($user) {
        if (! $user->facebook_id) {
            $user->update(['facebook_id' => $fbUser->getId(), 'avatar' => $fbUser->getAvatar()]);
        }
    } else {
        $user = User::create([
            'name'              => $fbUser->getName(),
            'email'             => $fbUser->getEmail(),
            'facebook_id'       => $fbUser->getId(),
            'avatar'            => $fbUser->getAvatar(),
            'email_verified_at' => now(),
        ]);
        $user->assignRole('customer');
    }

    Auth::login($user, remember: true);
    return redirect()->intended(route('app.landing'));
})->name('app.auth.facebook.callback');

Route::post('/app/auth/logout', function (Request $request) {
    Auth::logout();
    $request->session()->invalidate();
    $request->session()->regenerateToken();
    return redirect()->route('app.auth.login');
})->name('app.auth.logout')->middleware('auth');


// ─── Public Routes ────────────────────────────────────────────────────────────
Route::get('/', function () {
    $liveStreams = Stream::with('channel')
        ->where('status', 'live')
        ->orderByDesc('started_at')
        ->take(4)
        ->get();

    return view('app.landing', [
        'liveStreams' => $liveStreams,
        'heroStream'  => $liveStreams->first(),
    ]);
})->name('app.landing');

// ─── Protected App Routes ─────────────────────────────────────────────────────

Route::middleware('auth')->group(function () {

    Route::get('/app/stream/{stream}', function (Stream $stream) {
        $stream->load(['channel', 'activeStreamProduct.product']);
        return view('app.stream', compact('stream'));
    })->name('app.stream');

    Route::get('/app/broadcast/{stream}', function (Stream $stream) {
        $stream->load(['channel', 'activeStreamProduct.product']);
        return view('app.broadcast', compact('stream'));
    })->name('app.broadcast');

    Route::post('/app/stream/{stream}/go-live', function (Stream $stream) {
        abort_unless(app()->isLocal(), 403);
        $stream->update(['status' => 'live', 'started_at' => now()]);
        return response()->json(['status' => 'live']);
    });

    Route::post('/app/stream/{stream}/end', function (Stream $stream) {
        abort_unless(app()->isLocal(), 403);
        $stream->update(['status' => 'ended', 'ended_at' => now()]);
        return response()->json(['status' => 'ended']);
    });

    Route::get('/agora/token', function (Request $request, AgoraService $agora) {
        $channel = $request->string('channel')->toString();
        $uid     = $request->integer('uid', 0);
        $role    = $request->string('role', 'viewer')->toString();

        abort_if(blank($channel), 422, 'Channel name required.');

        $token = $role === 'host'
            ? $agora->hostToken($channel, $uid)
            : $agora->viewerToken($channel, $uid);

        return response()->json(compact('token'));
    });
});





// ─── Dev Only ─────────────────────────────────────────────────────────────────

Route::get('/database/fresh', function () {
    abort_unless(app()->isLocal(), 403);

    $tables = [
        'order_addresses',
        'order_items',
        'orders',
        'channel_follows',
        'stream_category',
        'donations',
        'categories',
        'product_claims',
        'stream_products',
        'products',
        'stream_chat_messages',
        'stream_recordings',
        'streams',
        'channels',
        'conversation_messages',
        'conversations',
        'subscriptions',
        'subscription_plans',
        'model_has_roles',
        'model_has_permissions',
        'role_has_permissions',
        'roles',
        'permissions',
        'users',
    ];

    DB::statement('SET FOREIGN_KEY_CHECKS=0');
    foreach ($tables as $table) {
        DB::table($table)->truncate();
    }
    DB::statement('SET FOREIGN_KEY_CHECKS=1');

    Artisan::call('db:seed', ['--force' => true]);

    return response()->json([
        'status'         => 'success',
        'message'        => 'Database truncated and re-seeded.',
        'tables_cleared' => count($tables),
        'seeded'         => ['RoleSeeder', 'AdminUserSeeder', 'SubscriptionPlanSeeder'],
        'login'          => ['url' => url('/zpanel/login'), 'email' => 'reg@ziontek.co', 'password' => '***'],
    ], 200, [], JSON_PRETTY_PRINT);
});

Route::get('/database/testdata', function () {
    abort_unless(app()->isLocal(), 403);

    $streamer = User::firstOrCreate(
        ['email' => 'elias@aurelian.test'],
        ['name' => 'Elias Thorne', 'password' => Hash::make('password'), 'email_verified_at' => now()]
    );
    $streamer->syncRoles(['streamer']);

    $customer = User::firstOrCreate(
        ['email' => 'customer@aurelian.test'],
        ['name' => 'Julianna Thorne', 'password' => Hash::make('password'), 'email_verified_at' => now()]
    );
    $customer->syncRoles(['customer']);

    $channel = Channel::firstOrCreate(
        ['slug' => 'elias-thorne-gems'],
        ['user_id' => $streamer->id, 'name' => 'Elias Thorne Gems', 'description' => 'Master Gemologist — rare diamonds and luxury pieces.', 'is_active' => true]
    );

    $stream = Stream::firstOrCreate(
        ['agora_channel_name' => 'aurelian-live-001'],
        ['channel_id' => $channel->id, 'title' => 'AURELIAN Diamond Solitaire — Live Auction', 'description' => 'Exclusive live auction of our most coveted diamond collection.', 'status' => 'live', 'started_at' => now(), 'peak_viewer_count' => 1247]
    );

    $diamond = Product::firstOrCreate(
        ['sku' => 'AUR-DS-001'],
        ['name' => 'AURELIAN Diamond Solitaire Ring', 'slug' => 'aurelian-diamond-solitaire-ring', 'description' => '2.1ct VS1 Brilliant Cut Diamond, 18k Yellow Gold Setting.', 'price' => 124990.00, 'stock_quantity' => 1, 'is_active' => true]
    );

    $bracelet = Product::firstOrCreate(
        ['sku' => 'AUR-GB-002'],
        ['name' => 'Celestial Gold Bracelet', 'slug' => 'celestial-gold-bracelet', 'description' => '22k gold tennis bracelet with sapphire accents.', 'price' => 48500.00, 'stock_quantity' => 3, 'is_active' => true]
    );

    StreamProduct::where('stream_id', $stream->id)->update(['is_active' => false]);
    StreamProduct::firstOrCreate(['stream_id' => $stream->id, 'product_id' => $diamond->id], ['is_active' => true, 'display_order' => 1, 'activated_at' => now()]);
    StreamProduct::firstOrCreate(['stream_id' => $stream->id, 'product_id' => $bracelet->id], ['is_active' => false, 'featured_price' => 42000.00, 'display_order' => 2]);
    StreamProduct::where('stream_id', $stream->id)->where('product_id', $diamond->id)->update(['is_active' => true, 'activated_at' => now()]);

    return response()->json([
        'status'  => 'success',
        'message' => 'Demo data ready.',
        'users'   => [
            'streamer' => ['email' => $streamer->email, 'password' => 'password', 'role' => 'streamer'],
            'customer' => ['email' => $customer->email, 'password' => 'password', 'role' => 'customer'],
        ],
        'urls' => [
            'landing'          => url('/'),
            'stream'           => url("/app/stream/{$stream->id}"),
            'broadcast'        => url("/app/broadcast/{$stream->id}"),
            'customer_login'   => url('/app/auth'),
            'streamer_login'   => url('/app/auth/streamer'),
            'panel'            => url('/zpanel'),
        ],
    ], 200, [], JSON_PRETTY_PRINT);
});
