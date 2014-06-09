<?php namespace Bcscoder\Jcheckout;

use Illuminate\Support\ServiceProvider;

class JcheckoutServiceProvider extends ServiceProvider {

	/**
	 * Indicates if loading of the provider is deferred.
	 *
	 * @var bool
	 */
	protected $defer = false;

	/**
	 * Bootstrap the application events.
	 *
	 * @return void
	 */
	public function boot()
	{
		$this->package('bcscoder/jcheckout');
		include __DIR__.'/../../routes.php';
		include __DIR__.'/../../myhelper.php';
	}

	/**
	 * Register the service provider.
	 *
	 * @return void
	 */
	public function register()
	{
		$this->app['jcheckout'] = $this->app->share(function($app)
        {
            return new JCheckout;
        });
	}

	/**
	 * Get the services provided by the provider.
	 *
	 * @return array
	 */
	public function provides()
	{
		return array('jcheckout');
	}

}
