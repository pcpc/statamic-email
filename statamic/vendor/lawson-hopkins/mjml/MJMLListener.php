<?php

namespace Statamic\Addons\MJML;

use Statamic\Extend\Listener;

class MJMLListener extends Listener
{
    /**
     * The events to be listened for, and the methods to call.
     *
     * @var array
     */
    public $events = [
        'cp.entry.published' => 'handle'
    ];

    public function debug_to_console( $data ) {
        $output = $data;
        if ( is_array( $output ) )
            $output = implode( ',', $output);
    
        echo "<script>console.log( 'Debug Objects: " . $output . "' );</script>";
    }

    public function makeEntryAwesome($entry)
    {
      $entry['title'] = 'awesome man';

      /* $entry->set('title', 'awesome man');

      $entry->save(); */
    
      return $entry;
    }

    public function console($data) {
        echo("<script>console.log('PHP: ".$data."');</script>");
    }

    public function handle (Entry $entry) {
        console('MJML handle function fired');
        makeEntryAwesome($entry);
        debug_to_console('$entry');
    }

}
