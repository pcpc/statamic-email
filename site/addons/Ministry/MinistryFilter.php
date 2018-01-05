<?php

namespace Statamic\Addons\Ministry;

use Statamic\Extend\Filter;

class MinistryFilter extends Filter
{
    /**
     * Perform filtering on a collection
     *
     * @return \Illuminate\Support\Collection
     */
    public function filterss($slug)
    {
        $col = Collection::handles();
        //var_dump($col);
        $coll = Collection::whereHandle('email');
        //echo ('hi1');
        var_dump($coll);
        $slug->each(function ($item, $key) { 
            $slug_string = $item;
            $coll = Collection::whereHandle('email');
            return $coll->filter(function ($entry) {
                return str_contains(
                    $entry->get('ministry'),
                    $slug_string
                );
            });
            //return 'hi';
        });
    }
}
