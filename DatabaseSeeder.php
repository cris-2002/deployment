<?php

namespace Database\Seeders;

use App\Models\Permission;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::factory()->create([
            'name' => 'Super Admin',
            'email' => 'superadmin@example.com',
        ]);
        User::factory()->create([
            'name' => 'Admin',
            'email' => 'admin@example.com',
        ]);
        User::factory()->create([
            'name' => 'cashier',
            'email' => 'cashier@example.com',
        ]);
        User::factory()->create([
            'name' => 'demo',
            'email' => 'demo@example.com',
        ]);
        // User::factory(5)->create();

        // seed pages permision
        $Pages = ['role', 'permission', 'category', 'product', 'review', 'pos', 'order', 'sales', 'cart', 'dashboard', 'user', 'allergy'];
        $PagesCrud = ['create', 'read', 'update', 'delete'];
        foreach ($Pages as $Page) {
            foreach ($PagesCrud as $PageCrud) {
                Permission::factory()->create([
                    'name' => $PageCrud.' '.$Page,
                ]);
            }
        }

        //seed role
        DB::table('roles')->insert([
            [
                'name' => 'super-admin',
                'guard_name' => 'web',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'admin',
                'guard_name' => 'web',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'staff',
                'guard_name' => 'web',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'customer',
                'guard_name' => 'web',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        //seed model_has_roles
        DB::table('model_has_roles')->insert([
            [
                'role_id' => '1',
                'model_type' => 'App\Models\User',
                'model_id' => '1',
            ],
            [
                'role_id' => '2',
                'model_type' => 'App\Models\User',
                'model_id' => '2',
            ],
            [
                'role_id' => '3',
                'model_type' => 'App\Models\User',
                'model_id' => '3',
            ],
            [
                'role_id' => '4',
                'model_type' => 'App\Models\User',
                'model_id' => '4',
            ],
        ]);

        $permissions = [];
        $lvl = [];
        $startlevel = 1;

        foreach ($Pages as $key) {
            for ($i = 0; $i < 4; $i++) {
                $lvl[] = $startlevel;
                $startlevel++;
            }
            $permissions[$key] = $lvl;
            $lvl = [];
        }

        $adminpermissions = ['role', 'category', 'product', 'review', 'pos', 'order', 'sales', 'cart', 'dashboard', 'user'];
        foreach ($adminpermissions as $adminpermission) {
            foreach ($permissions[$adminpermission] as $permission) {
                DB::table('role_has_permissions')->insert([
                    [
                        'permission_id' => $permission,
                        'role_id' => '2',
                    ],
                ]);
            }
        }

        $staffpermissions = ['order', 'pos', 'cart', 'dashboard', 'sales'];
        foreach ($staffpermissions as $staffpermission) {
            foreach ($permissions[$staffpermission] as $permission) {
                DB::table('role_has_permissions')->insert([
                    [
                        'permission_id' => $permission,
                        'role_id' => '3',
                    ],
                ]);
            }
        }

        $customerpermissions = ['order', 'cart'];
        foreach ($customerpermissions as $customerpermission) {
            foreach ($permissions[$customerpermission] as $permission) {
                if ($permission != '27' && $permission != '28') {
                    DB::table('role_has_permissions')->insert([
                        [
                            'permission_id' => $permission,
                            'role_id' => '4',
                        ],
                    ]);
                }
            }
        }

        // run class seeder

        $this->call([
            CategorySeeder::class, //php artisan db:seed --class=CategorySeeder
            AllergySeeder::class, //php artisan db:seed --class=AllergySeeder
            ProductSeeder::class, //php artisan db:seed --class=ProductSeeder
            ReviewSeeder::class, //php artisan db:seed --class=ReviewSeeder
            ProductAllergySeeder::class, //php artisan db:seed --class=ProductAllergySeeder
        ]);

    }
}
