<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PetController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\AdoptionController;
use App\Http\Controllers\LogisticsController;
use App\Http\Controllers\BazarController;
use App\Http\Controllers\DiaryController;
use App\Http\Controllers\CommunityController;
use App\Http\Controllers\VaultController;
use App\Http\Controllers\DonationController;
use App\Http\Controllers\SponsorshipController;
use App\Http\Controllers\PushSubscriptionController;
use App\Http\Controllers\MunicipalityController;
use App\Http\Controllers\PublicStoryController;

Route::get('/', function () {
    $featuredPets = \App\Models\Pet::whereIn('name', ['Kira', 'Milo', 'Thor'])
        ->get(['id', 'name', 'species', 'breed', 'age_text', 'photo_path', 'status']);

    $stats = [
        'pets_count' => \App\Models\Pet::count(),
        'trips_count' => \App\Models\UberTrip::where('status', 'completado')->count(),
        'entrepreneurs_count' => 3, // Representa la cantidad de Pymes registradas en el Bazar hardcodeado
        'users_count' => \App\Models\User::count(),
    ];

    return Inertia::render('Welcome', [
        'featuredPets' => $featuredPets,
        'launchStats' => $stats
    ]);
});

// Rutas Públicas (Información y Catálogos)
Route::get('/nosotros', function () {
    return Inertia::render('About');
})->name('about');

Route::get('/bazar', [BazarController::class, 'showBazarHub'])->name('bazar.hub');
Route::get('/pets', [PetController::class, 'index'])->name('pets.index');
Route::get('/pets/{pet}/story', [PublicStoryController::class, 'showStory'])->name('pets.story');
Route::get('/adoptions/{adoption}/diaries/{diary}/photo', [DiaryController::class, 'showPhoto'])->name('adoptions.diaries.photo');


// Rutas de Apadrinamiento y QR Pass (Padrino Virtual)
Route::get('/pets/{pet}/qr-pass', [SponsorshipController::class, 'showQrPass'])->name('pets.qr-pass');
Route::post('/pets/{pet}/report-found', [SponsorshipController::class, 'reportFound'])->name('pets.report-found');
Route::get('/pets/{pet}/sponsor', [SponsorshipController::class, 'showSponsorForm'])->name('pets.sponsor');
Route::post('/pets/{pet}/sponsor/process', [SponsorshipController::class, 'processSponsorship'])->name('pets.sponsor.process');

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    
    // Panel Dashboard Base
    Route::get('/dashboard', function () {
        $user = Auth::user();
        if ($user->role === 'municipalidad') {
            return redirect()->route('municipality.dashboard');
        }
        $pets = [];
        $sponsoredPets = [];
        
        if (in_array($user->role, ['admin', 'fundacion', 'rescatista'])) {
            $pets = \App\Models\Pet::whereNotNull('latitude')
                ->whereNotNull('longitude')
                ->get(['id', 'name', 'species', 'status', 'latitude', 'longitude']);
        }

        if (in_array($user->role, ['adoptante', 'transito', 'donante'])) {
            $sponsoredPets = \App\Models\Pet::whereHas('sponsorships', function ($q) use ($user) {
                $q->where('user_id', $user->id)->where('status', 'active');
            })->get(['id', 'name', 'species', 'status', 'photo_path']);
        }

        $user->load('badges');
        $milestoneService = new \App\Services\MilestoneService();
        $milestoneService->initializeMilestones();
        $globalMilestones = \App\Models\SystemMilestone::get();

        return Inertia::render('Dashboard', [
            'user' => $user,
            'pets' => $pets,
            'sponsoredPets' => $sponsoredPets,
            'badges' => $user->badges,
            'globalMilestones' => $globalMilestones,
        ]);
    })->name('dashboard');

    // Perfil y Estilo de Vida del Adoptante
    Route::get('/adopter/profile', [UserController::class, 'showProfileForm'])->name('adopter.profile');
    Route::post('/adopter/profile', [UserController::class, 'updateProfile']);

    // Flujo de Adopciones y Firmas
    Route::get('/adoptions', [AdoptionController::class, 'index'])->name('adoptions.index');
    Route::post('/adoptions', [AdoptionController::class, 'store'])->name('adoptions.store');
    Route::get('/adoptions/{adoption}', [AdoptionController::class, 'show'])->name('adoptions.show');
    Route::post('/adoptions/{adoption}/status', [AdoptionController::class, 'updateStatus'])->name('adoptions.status');
    Route::get('/adoptions/{adoption}/template', [AdoptionController::class, 'downloadTemplate'])->name('adoptions.template');
    Route::get('/adoptions/{adoption}/download-signed', [AdoptionController::class, 'downloadSigned'])->name('adoptions.download-signed');
    Route::post('/adoptions/{adoption}/sign-canvas', [AdoptionController::class, 'signCanvas'])->name('adoptions.sign-canvas');
    Route::post('/adoptions/{adoption}/sign-upload', [AdoptionController::class, 'signUpload'])->name('adoptions.sign-upload');

    // Diarios de Seguimiento y S.O.S
    Route::post('/adoptions/{adoption}/diaries', [DiaryController::class, 'store'])->name('adoptions.diaries.store');
    Route::post('/adoptions/{adoption}/sos', [DiaryController::class, 'requestSos'])->name('adoptions.sos');
    Route::post('/adoptions/{adoption}/upload-sterilization', [AdoptionController::class, 'uploadSterilization'])->name('adoptions.upload-sterilization');

    // Logística y Uber Solidario
    Route::get('/logistics', [LogisticsController::class, 'showLogisticsHub'])->name('logistics.hub');
    Route::post('/logistics/uber', [LogisticsController::class, 'requestTrip'])->name('logistics.uber.request');
    Route::post('/logistics/uber/{trip}/accept', [LogisticsController::class, 'acceptTrip'])->name('logistics.uber.accept');
    Route::post('/logistics/uber/{trip}/complete', [LogisticsController::class, 'completeTrip'])->name('logistics.uber.complete');
    
    // Bazar y Club de Huellas (Gamificación)
    Route::post('/bazar/claim-reward', [BazarController::class, 'claimReward'])->name('bazar.claim-reward');
    Route::post('/bazar/premium', [BazarController::class, 'purchasePremiumPlan'])->name('bazar.premium');

    // Radar Pet-Friendly y Paseos en Manada
    Route::get('/community/radar', [CommunityController::class, 'showRadar'])->name('community.radar');
    Route::post('/community/spots', [CommunityController::class, 'storeSpot'])->name('community.spots.store');
    Route::post('/community/walks', [CommunityController::class, 'storeWalk'])->name('community.walks.store');

    // Donaciones y Trazabilidad Financiera
    Route::get('/donations/traceability', [DonationController::class, 'showTraceability'])->name('donations.traceability');
    Route::post('/donations/checkout', [DonationController::class, 'initiateDonation'])->name('donations.checkout');



    // Rutas administrativas del Backoffice
    Route::middleware('role:admin,fundacion,rescatista')->prefix('backoffice')->group(function () {
        Route::resource('pets', PetController::class)->except(['index']);
        Route::get('/contracts-vault', [VaultController::class, 'showVault'])->name('backoffice.vault');
        Route::get('/audit-logs', [VaultController::class, 'showAuditLogs'])->name('backoffice.audit-logs');
        
        // Moderación de bitácoras públicas
        Route::get('/moderation', [\App\Http\Controllers\Backoffice\ModerationController::class, 'index'])->name('backoffice.moderation.index');
        Route::post('/moderation/{diary}/approve', [\App\Http\Controllers\Backoffice\ModerationController::class, 'approve'])->name('backoffice.moderation.approve');
        Route::post('/moderation/{diary}/reject', [\App\Http\Controllers\Backoffice\ModerationController::class, 'reject'])->name('backoffice.moderation.reject');
    });

    // Rutas de Municipalidad
    Route::middleware('role:municipalidad')->prefix('municipality')->group(function () {
        Route::get('/dashboard', [MunicipalityController::class, 'index'])->name('municipality.dashboard');
        Route::post('/operatives', [MunicipalityController::class, 'storeOperative'])->name('municipality.operatives.store');
        Route::get('/audit-abuse', [MunicipalityController::class, 'inspectAbuseFlags'])->name('municipality.audit-abuse');
    });

    // Suscripciones Push
    Route::post('/api/push-subscriptions', [PushSubscriptionController::class, 'subscribe']);
    Route::post('/api/push-subscriptions/delete', [PushSubscriptionController::class, 'unsubscribe']);
});
