<?php

namespace Statamic\Addons\FilteredEmails;

use Statamic\Extend\Widget;

use Statamic\API\User;
use Statamic\API\UserGroup;
use Statamic\API\Collection;
//use Statamic\Addons\Ministry;
use Statamic\API\Entry;

//define("DEV_SITE_URL_2", $_SERVER['HTTP_HOST']);

class FilteredEmailsWidget extends Widget
{
    /**
     * The HTML that should be shown in the widget
     *
     * @return string
     */
    public function html()
    {
        

        $emails = $this->getEmails();

        $html = '<div class="card flush">
            <div class="head">
                <h1><a href="/cp/collections/entries/email">Emails</a></h1>
                <a href="/cp/collections/entries/email/create" class="btn btn-primary">Add New Email</a>
            </div>
            <div class="card-body pad-16">
                <table class="dossier">
                    <tbody>';
                    //dd($emails);
                    $trys = $emails->map(function ($email, $key) {
                        //var_dump($email);
                        //dd($email);
                        //dd($email->$key);
                        //dd($email);
                        return $email->map(function ($item, $k) {
                            $goods = [ 
                                'title' => $item->get('title'),
                                'slug_url' => $item->slug(),
                                'date' => $item->order()
                            ];
                            return $goods;
                        });
                        
                    });
                    //dd($trys);

                    foreach ($trys as $try) {
                        //dd($try);
                        foreach ($try as $t) {
                            $title = $t['title'];
                            $slug_url = $t['slug_url'];
                            $date = $t['date'];
                            $html .= '<tr>
                            <td><a href="/cp/collections/entries/email/' . $slug_url . '">'.$title.'</a></td>
                            <td class="minor text-center"></td>
                            <td class="minor text-right">'.$date.'</td>
                        </tr>';
                        }
                        //$email=$email->toArray();
                        //$try = $email->map(function ($email))
                        //var_dump($email->order);
                        //$title = $email['title'];
                        //$slug_url = $email['slug'];
                        $title = "title";//$email->title;
                        $slug_url = 'slug';//$email->slug;
                        
                    }
                    
                    

        /* $content .= $emails->map(function ($item, $key) use ($html) { 
            //$slug_url = $item->slug();
           
        //$html .=  $content;
        $html .=    '</tbody>
                </table>
            </div>
        </div>';

        return $html;*/

    }


    /* public function getUserGroups() {
        $user = User::getCurrent();
        $groups = $user->groups();
        return $groups;
    } */

    public function filterEmails($slug)
    {
        
        //$coll = Collection::whereHandle('email');
        //$coll = Entry::whereCollection('email');
        //echo ('hi1');
        //var_dump($coll);
        $returns = array();
        return $slug->map(function ($item, $key) use ($returns) { 
            $slug_string = $item;
            return $this->secondFilter($slug_string);
            // var_dump($full_entry);
            /* $coll = Entry::whereCollection('email');
            
            return $coll->filter(function ($entry) {
                //$slug_string = $item;
                return str_contains(
                    $entry->get('ministry'),
                    $slug_string
                );
            }); */
            //return 'hi';
        });
        //return $r;
    }

/*     public function filterEmails2($slug)
    {
        
        //$coll = Collection::whereHandle('email');
        $coll = Entry::whereCollection('email');
        //echo ('hi1');
        //var_dump($slug);
        $returns = array();
        //global $stuff2;
        $stuff2 = array();
        $stuff3 = $slug->map(function ($item, $key) use ($stuff2) { 
            $slug_string = $item;
            $coll = Entry::whereCollection('email');
            $stuff = $coll->filter(function ($entry) use ($slug_string) {

                $id = $entry->get('ministry')[0];
                $min_slug = Entry::find($id)->slug();

                return false;
            });
            $stuff = $item;
            array_push($stuff2, $stuff);

        });
        var_dump($stuff3);

    } */





    public function secondFilter($slug_string) {

        //$slug_string2 = array();
        //var_dump($slug_string);
        //array_push($slug_string2, $slug_string);
        $coll = Entry::whereCollection('email');
        return $coll->filter(function ($entry) use ($slug_string) {
            /* return in_array(
                $entry->get('ministry'),
                $slug_string2
            ); */


            //$id = $entry->get('ministry')[0];
            $id = $entry->get('ministry');
            //var_dump($id);
            //echo('meowman');
            foreach ($id as $key => $value) {
                $min_slug = Entry::find($value)->slug();
                $test = str_contains(
                    $min_slug,
                    $slug_string
                );
                if ($test == true) {
                    //echo 'MATCH: ' . $min_slug . ' = ' . $slug_string . '-------------';    
                    return true;
                } else {
                    //return false;
                }
                //echo $min_slug . ' = ' . $slug_string . '------------';
            }
            return false;
            /* $min_slug = Entry::find($id)->slug();
            echo $min_slug;
            echo $slug_string; */
            /* return str_contains(
                $min_slug,
                $slug_string
            ); */



            //var_dump($entry->get('ministry')->get('title'));
            
            //var_dump($title);
        });
        /* return $coll->filter(function ($entry) {
            return str_contains(
                $entry->get('ministry'),
                $slug_string
            );
        }); */

    }

    public function getEmails() {
        $content = array();
        $user = User::getCurrent();
        $groups = $user->groups();
        //$handles = 
        /* foreach ($groups as $g) {
            $info = $this->filter($g);
            array_push($content, $info);
        } */

        //var_dump($groups);

        //$hm = UserGroup::whereHandle('pcpc_men');

        //$val = ' blah ';//$hm->getParam('slug');

        //var_dump($groups);

        //var_dump($groups);

        //print_r($groups);

        //$val = data_get($groups, 'items.slug');

        //return var_dump($groups->first()->slug);

        //$groups->all();

        // $groups = array_get($_POST, 'pcpcemail.groups', []);

        //var_dump($groups);

        
        //$current_user_groups = array();

        //$all_groups = UserGroup::all();
        
        $slug_collection = User::getCurrent()->groups()->map(function ($group) {
            //array_push($current_user_groups, $group->slug());
            /* $g = $group->slug();
            $all_groups = UserGroup::all();
            $all_groups->map(function ($all) {
                return $all->slug();
            }); */
            return $group->slug();
        });

        return $this->filterEmails($slug_collection);

        

        //var_dump($current_user_groups);

        /* $current_user_groups = $current_user_groups->each(function ($item, $key) {
            //var_dump($item);
            //var_dump(data_get($item, 'id.slug'));
            //echo $item['slug'];
            //echo $this->slug;
            //var_dump($item->slug);
            //var_dump($item);
        }); */

        /* $val = $groups->get('slug'); */
        
        //$val = UserGroup::all();
        
        // return var_dump($val);
    }



}