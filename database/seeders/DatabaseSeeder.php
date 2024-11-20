<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(CategorySeeder::class);
        $this->call(TagSeeder::class);

        $permissions = [
            'manage posts',
            'publish posts',
            'edit posts',
            'delete posts',
            'manage categories',
            'manage tags',
            'manage users'
        ];

        foreach ($permissions as $permission) {
            Permission::create(['name' => $permission]);
        }

        $admin = Role::create(['name' => 'Admin']);
        $editor = Role::create(['name' => 'Editor']);
        $author = Role::create(['name' => 'Author']);
        $reader = Role::create(['name' => 'Reader']);

        $admin->givePermissionTo($permissions);
        $editor->givePermissionTo([
            'manage posts',
            'publish posts',
            'edit posts',
            'delete posts',
            'manage categories',
            'manage tags'
        ]);
        $author->givePermissionTo([
            'manage posts',
            'publish posts',
            'edit posts'
        ]);
        $reader->givePermissionTo([]);

        $user = User::create([
            'name' => 'Admin',
            'email' => 'admin@gmail.com',
            'password' => bcrypt('123456QQ')
        ]);
        $user->assignRole('Admin');
    }
}
