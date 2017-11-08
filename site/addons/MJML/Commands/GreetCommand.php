<?php

namespace Statamic\Addons\MJML\Commands;

use Statamic\Extend\Command;

class GreetCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mjml:greet
                            {name=World : The name to be greeted}
                            {--shout= : Whether to shout or not}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Say hello!';

    /**
     * Create a new command instance.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $message = 'Hello ' . $this->argument('name');
        
        if ($this->option('shout')) {
            $message .= '!!!';
        }
        
        $this->info($message);
    }
}
