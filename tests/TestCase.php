<?php

namespace AvocetShores\Conduit\Tests;

use AvocetShores\Conduit\ConduitServiceProvider;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Encryption\Encrypter;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();

        Factory::guessFactoryNamesUsing(
            fn (string $modelName) => 'AvocetShores\\Conduit\\Database\\Factories\\'.class_basename($modelName).'Factory'
        );

        // $this->setUpDatabase();
    }

    protected function getPackageProviders($app)
    {
        return [
            ConduitServiceProvider::class,
        ];
    }

    public function getEnvironmentSetUp($app)
    {
        config()->set('rewind.database_connection', 'sqlite');
        config()->set('database.default', 'sqlite');
        config()->set('database.connections.sqlite', [
            'driver' => 'sqlite',
            'database' => ':memory:',
        ]);

        config()->set('app.key', 'base64:'.base64_encode(
            Encrypter::generateKey(config()['app.cipher'])
        ));

        /*
         foreach (\Illuminate\Support\Facades\File::allFiles(__DIR__ . '/database/migrations') as $migration) {
            (include $migration->getRealPath())->up();
         }
         */
    }

//    protected function setUpDatabase()
//    {
//        $this->runMigrations();
//    }
//
//    protected function runMigrations(): void
//    {
//        foreach (File::allFiles(__DIR__.'/../database/migrations') as $migration) {
//            (require $migration->getPathname())->up();
//        }
//
//        Schema::create('users', function (Blueprint $table) {
//            $table->id();
//            $table->string('name');
//            $table->string('email')->unique();
//            $table->timestamp('email_verified_at')->nullable();
//            $table->string('password');
//            $table->rememberToken();
//            $table->timestamps();
//        });
//
//        Schema::create('posts', function (Blueprint $table) {
//            $table->id();
//            $table->unsignedBigInteger('user_id');
//            $table->string('title')->nullable();
//            $table->text('body')->nullable();
//            $table->unsignedBigInteger('current_version')->nullable();
//            $table->timestamps();
//        });
//    }
}
