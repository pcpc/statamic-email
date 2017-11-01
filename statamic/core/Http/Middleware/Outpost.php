<?php

namespace Statamic\Http\Middleware;

use Closure;
use Statamic\API\Str;
use Statamic\Outpost as StatamicOutpost;

class Outpost
{
    /**
     * @var Request
     */
    private $request;

    /**
     * @var StatamicOutpost
     */
    private $outpost;

    /**
     * Create a new Middleware
     *
     * @param StatamicOutpost $outpost
     */
    public function __construct(StatamicOutpost $outpost)
    {
        $this->outpost = $outpost;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $this->request = $request;

        $this->outpost->radio();

        $this->setLicensing();

        $this->setUpdateAvailability();

        return $next($request);
    }

    /**
     * Set a notice if there is a newer version available
     *
     * @return void
     */
    private function setUpdateAvailability()
    {
        view()->share('update_available', $this->outpost->isUpdateAvailable());
    }

    /**
     * Set some view data related to licensing messages
     *
     * @return void
     */
    private function setLicensing()
    {
        view()->composer(['layout', 'partials.alerts'], function ($view) {
            $view->with('is_trial', $this->outpost->isTrialMode());
            $view->with('is_unlicensed', !$this->outpost->isLicenseValid());
        });

        view()->composer('partials.alerts', function ($view) {
            $view->with('license_issue', $this->hasLicenseIssue());
        });
    }

    private function hasLicenseIssue()
    {
        // They are on the licensing page, where they will be shown any issues.
        // We don't need to double up on the messages.
        if ($this->request->route()->getName() === 'licensing') {
            return false;
        }

        if ($this->outpost->isTrialMode()) {
            return false;
        }

        return !$this->outpost->isLicenseValid()
            || !$this->outpost->isOnCorrectDomain()
            || !$this->outpost->areAddonLicensesValid();
    }
}
