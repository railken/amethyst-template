<?php

namespace Railken\LaraOre;

use Illuminate\Foundation\AliasLoader;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\ServiceProvider;
use Railken\LaraOre\Api\Support\Router;

class TemplateServiceProvider extends ServiceProvider
{
    /**
     * The application instance.
     *
     * @var \Illuminate\Contracts\Foundation\Application
     */
    public $app;

    /**
     * Current version.
     *
     * @var string
     */
    public $version;

    /**
     * Bootstrap any application services.
     */
    public function boot(\Illuminate\Routing\Router $router)
    {
        $this->publishes([
            __DIR__.'/../config/ore.template.php' => config_path('ore.template.php'),
        ], 'config');

        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
        $this->loadRoutes();

        $this->loadViews();

        config(['ore.permission.managers' => array_merge(Config::get('ore.permission.managers', []), [
            \Railken\LaraOre\Template\TemplateManager::class,
        ])]);
    }

    /**
     * Register the service provider.
     */
    public function register()
    {
        $this->app->register(\Railken\Laravel\Manager\ManagerServiceProvider::class);
        $this->app->register(\Railken\LaraOre\ApiServiceProvider::class);
        $this->app->register(\TwigBridge\ServiceProvider::class);
        AliasLoader::getInstance()->alias('Twig', \TwigBridge\Facade\Twig::class);

        $this->mergeConfigFrom(__DIR__.'/../config/ore.template.php', 'ore.template');
    }

    /**
     * Load routes.
     */
    public function loadRoutes()
    {
        Router::group(array_merge(Config::get('ore.template.http.router'), [
            'namespace' => 'Railken\LaraOre\Http\Controllers',
        ]), function ($router) {
            $router->post('/render', ['uses' => 'TemplatesController@render']);
            $router->get('/', ['uses' => 'TemplatesController@index']);
            $router->post('/', ['uses' => 'TemplatesController@create']);
            $router->put('/{id}', ['uses' => 'TemplatesController@update']);
            $router->delete('/{id}', ['uses' => 'TemplatesController@remove']);
            $router->get('/{id}', ['uses' => 'TemplatesController@show']);
        });
    }

    /**
     * Load views.
     */
    public function loadViews()
    {
        $m = new \Railken\LaraOre\Template\TemplateManager();
        $path = $m->getPathTemplates();
        $m->loadViews();

        $this->loadViewsFrom($path, 'ore');
    }
}
