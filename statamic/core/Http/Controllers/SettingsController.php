<?php

namespace Statamic\Http\Controllers;

use Statamic\API\Config;
use Statamic\API\Folder;
use Statamic\API\Fieldset;
use Statamic\API\File;
use Statamic\API\YAML;
use Statamic\API\Helper;
use Statamic\API\Cache;
use Statamic\API\Stache;
use Statamic\Config\Settings;
use Statamic\CP\Publish\ProcessesFields;

class SettingsController extends CpController
{
    use ProcessesFields;

    protected $name;

    public function index()
    {
        return redirect()->route('settings.edit', 'system');
    }

    public function edit($name)
    {
        $data = $this->preProcessWithBlankFields(
            Fieldset::get($name, 'settings'),
            Config::get($name)
        );

        return view('settings.edit', [
            'title' => t('settings_'.$name),
            'extra' => [
                'env' => array_get(app(Settings::class)->env(), $name)
            ],
            'slug' => $name,
            'content_data' => $data,
            'content_type' => 'settings',
            'fieldset' => 'settings.'.$name,
        ]);
    }

    public function update($name)
    {
        $this->name = $name;

        $data = $this->processFields(Fieldset::get($this->name, 'settings'), $this->request->input('fields'));

        // Replace environment-managed values with their raw equivalents

        if ($environment_variables = $this->request->input('extra.env')) {
            foreach ($environment_variables as $key => $env) {
                $data[$key] = $env['raw'];
            }
        }

        $contents = YAML::dump($data);

        $file = settings_path($name . '.yaml');
        File::put($file, $contents);

        Cache::clear();
        Stache::clear();

        $this->success('Settings updated');

        return ['success' => true, 'redirect' => route('settings.edit', $name)];
    }

    public function licenseKey()
    {
        Config::set('system.license_key', $this->request->input('key'));

        return back();
    }
}
