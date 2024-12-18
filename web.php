<?php

use App\Http\Controllers\AllergyController;
use App\Http\Controllers\CaloryController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\Mobile\ProductController as MobileProductController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\PointofsaleController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReceiptController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\SaleController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schedule;

Route::get('/', function () {
    // return view('welcome');
    return redirect('login');
});

Route::get('test', function () {
    return view('test');
});

Route::get('/calcalories', [CaloryController::class, 'calculateCalories']);
Route::get('/receipt/{id}', [ReceiptController::class, 'index']);

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::group(['middleware' => ['auth', 'role:super-admin|admin']], function () {

    Route::resource('permissions', PermissionController::class);
    Route::delete('permissions/{permission}', [PermissionController::class, 'destroy']);
    Route::get('permissions-alldata', [PermissionController::class, 'alldata']);

    Route::resource('roles', RoleController::class);
    Route::delete('roles/{role}', [RoleController::class, 'destroy']);
    Route::get('roles-alldata', [RoleController::class, 'alldata']);

    Route::get('roles/{roleId}/give-permissions', [RoleController::class, 'addPermissionToRole']);
    Route::put('roles/{roleId}/give-permissions', [RoleController::class, 'givePermissionToRole']);

    Route::resource('users', UserController::class);
    Route::get('users/{userId}', [UserController::class, 'destroy']);
    Route::get('users-alldata', [UserController::class, 'alldata']);

});

Route::group(['middleware' => ['auth']], function () {

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::get('profile-alldata', [ProfileController::class, 'allprofileinfo']);

    Route::resource('products', ProductController::class);
    Route::resource('pos', PointofsaleController::class);
    Route::resource('categories', CategoryController::class);
    Route::resource('allergies', AllergyController::class);
    Route::resource('reviews', ReviewController::class);
    Route::resource('orders', OrderController::class);
    Route::resource('carts', CartController::class);

    Route::get('sales', [SaleController::class, 'index']);
    Route::get('sales-alldata', [SaleController::class, 'alldata']);

    Route::get('orders-alldata', [OrderController::class, 'alldata']);
    Route::get('orders-product-alldata/{id}', [OrderController::class, 'order_product_alldata']);

    Route::delete('cartdeleteAllForUser/{id}', [CartController::class, 'deleteAllForUser']);

    Route::get('pos-alldata', [PointofsaleController::class, 'allproductpublish']);
    Route::get('pos-userlist', [PointofsaleController::class, 'userlist']);

    Route::get('carts-alldata', [CartController::class, 'alldata']);
    Route::get('carts-userinfo', [CartController::class, 'userinfo']);


    Route::get('categories-alldata', [CategoryController::class, 'alldata']);
    Route::delete('categories/{id}', [CategoryController::class, 'destroy']);

    Route::get('allergies-alldata', [AllergyController::class, 'alldata']);
    Route::delete('allergies/{id}', [CategoryController::class, 'destroy']);

    Route::get('products-alldata', [ProductController::class, 'alldata']);
    Route::delete('products/{id}', [ProductController::class, 'destroy']);

    Route::get('reviews-alldata', [ReviewController::class, 'alldata']);
    Route::delete('reviews/{id}', [ReviewController::class, 'destroy']);

    Route::get('/get-csrf-token', function () {
        return response()->json(['token' => csrf_token()]);
    });

    Route::resource('shop', MobileProductController::class);
    Route::get('mproduct-alldata/{id}', [MobileProductController::class, 'alldata']);
    Route::post('store-customer-payment', [OrderController::class, 'store_customer']);
    Route::post('store-addreview', [OrderController::class, 'addreview']);
    Route::put('order-updatestatus', [OrderController::class, 'updatestatus']);

    Route::get('order-checkstatus', [OrderController::class, 'checkstatus']);

});

// Route::delete('categories/{id}/destroy', [RoleController::class, 'destroy']);


// Schedule::job()->everyMinute();
Schedule::command('update-calories-credits')->everyMinute();

require __DIR__.'/auth.php';
