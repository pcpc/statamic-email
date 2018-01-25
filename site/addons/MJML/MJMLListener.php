<?php

namespace Statamic\Addons\MJML;

use Statamic\Extend\Listener;
use Michelf\MarkdownExtra;

class MJMLListener extends Listener
{
    /**
     * The events to be listened for, and the methods to call.
     *
     * @var array
     */
    public $events = [
        'cp.entry.published' => 'createMJMLCode'
    ];

    

    /* public function debug_to_console( $data ) {
        $output = $data;
        if ( is_array( $output ) )
            $output = implode( ',', $output);
    
        echo "<script>console.log( 'Debug Objects: " . $output . "' );</script>";
    } */

    

    /* public function console($data) {
        echo("<script>console.log('PHP: ".$data."');</script>");
    } */

    

    public function createMJMLCode ($entry)
    {

      /* BZ - change on localhost for images to work if testing MJML code */
      //$protocol = stripos($_SERVER['SERVER_PROTOCOL'], 'https') === true ? 'https://' : 'http://';
      $protocol = stripos($_SERVER['SERVER_PROTOCOL'], 'https') === false ? 'http://' : 'https://';
      define("DEV_SITE_URL", $_SERVER['SERVER_PROTOCOL'] . $protocol . $_SERVER['HTTP_HOST']);

      $blocks = $entry->get('content_blocks');

      // don't do anything unless the type of entry is 'email' (i.e. has blocks)

      if (is_null($blocks)) {
          return;
      }

      $block_count = count($blocks);

      $block_types = array();

      for ($i = 0; $i < $block_count; $i++) {
          $entry->set('block-'.$i.'-type', $blocks[$i]['type']);
          array_push($block_types, $blocks[$i]['type']);
      }

      $mjml_code = '<mjml>';
      /* will need to determine how to add head inline-style classes only once, only as needed, probably cycle through all distinct block types */
      $mjml_head = '<mj-head>';
      // add styles to all
      $mjml_head .= '<mj-attributes><mj-text padding-top="0" padding-bottom="0" /></mj-attributes>';
      $mjml_body = '<mj-body><mj-container>';
      $mjmj_styles = '';

      for ($i=0; $i < count($block_types); $i++) {
        switch ($block_types[$i]) {
            case 'quote':
                $mjml_body .= $this->addMJMLBodyQuote($blocks[$i]['quote_text'], $blocks[$i]['quote_author']);
                break;
            case 'hero_image':
                $mjml_body .= $this->addMJMLBodyHeroImage($blocks[$i]['image'], $blocks[$i]['hero_text']);
                break;
            case 'text_block':
                $mjml_body .= $this->addMJMLBodyTextBlock($blocks[$i]['email_text']);
                break;
            case 'text_and_image':
                if (!(isset($blocks[$i]['text_and_image_header']))) {
                    $text_and_image_header = '';
                } else {
                    $text_and_image_header = $blocks[$i]['text_and_image_header'];
                }
                $mjml_body .= $this->addMJMLBodyTextAndImage($text_and_image_header, $blocks[$i]['text_and_image_text'], $blocks[$i]['text_and_image_image'], $blocks[$i]['arrangement']);
                break;
            case 'signature_image':
                $mjml_body .= $this->addMJMLBodySignatureImage($blocks[$i]['signature']);
                break;
            case 'table':
                if ( !(in_array('first_row_heading', $blocks[$i])) ) {
                    $first_row_heading_toggle = false;
                } else {
                    $first_row_heading_toggle = $blocks[$i]['first_row_heading'];
                }
                $mjml_body .= $this->addMJMLBodyTable($blocks[$i]['table_field'], $first_row_heading_toggle);
                break;
            case 'button':
                $mjml_body .= $this->addMJMLBodyButton($blocks[$i]['button_text'], $blocks[$i]['button_link']);
                break;
            case 'divider':
                if (!(isset($blocks[$i]['divider_text']))) {
                    $divider_text = '';
                } else {
                    $divider_text = $blocks[$i]['divider_text'];
                }
                $mjml_body .= $this->addMJMLBodyDivider($divider_text);
                break;
            case 'scripture':
                $mjml_body .= $this->addMJMLBodyScripture($blocks[$i]['reference']);
                break;
        }
      }

      $mjml_body .= $this->addMJMLFooter();

      $unique_block_types = array_unique($block_types);
      $inline_classes = array();

      foreach ($unique_block_types as $u) {
          switch ($u) {
              case 'quote':
                array_push($inline_classes, 'italic');
                break;
          }
      }

      $mjml_style_code = $this->addMJMLHeadInlineClasses($inline_classes);

      $mjml_head .= $mjml_style_code . '</mj-head>';
      $mjml_body .= '</mj-container></mj-body>';
      $mjml_code .= $mjml_head . $mjml_body . '</mjml>';

      $entry->set('mjml_code', $mjml_code);



      // CURL Request
      
      $mjml_app_id='0112c424-a153-4fd7-ac97-b3e1b412b84b';
      $mjml_sk='f9d570fc-f819-41ee-8acf-962fd75a2245';
      $url='https://api.mjml.io/v1/render';
      // may need double quotes around $userpwd
      $userpwd = $mjml_app_id.':'.$mjml_sk;

      $mjml_code_json = '{
                            "mjml": ' . json_encode($mjml_code) . '
      }';
      
      $ch = curl_init();
      curl_setopt($ch, CURLOPT_URL, $url);
      curl_setopt($ch, CURLOPT_TIMEOUT, 30);
      
      // curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_ANY);
      curl_setopt($ch, CURLOPT_USERPWD, '0112c424-a153-4fd7-ac97-b3e1b412b84b:3f447dbf-0aed-44fd-a60c-dbf614acb396');
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
      curl_setopt($ch, CURLOPT_POSTFIELDS, $mjml_code_json);
      $mjml_result=curl_exec($ch);
      $status_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);   //get status code
      curl_close($ch);

      // End CURL Request

      $mjml_result2 = json_decode($mjml_result, true);

      $entry->set('mjml_status_code', $status_code);
      $entry->set('mjml_errors', $mjml_result2['errors']);
      $entry->set('html', $mjml_result2['html']);

      $entry->save();
    
      return $entry;
    }

    public function handle ($entry) {
        /* console('MJML handle function fired'); */
        $this->createMJMLCode();
        /* debug_to_console('$entry'); */
    }

    public function addMJMLBodyQuote ($quote_text, $quote_author) {
        $string =   '<mj-section background-color="#ffffff">
                        <mj-column width="400">
                            <mj-text align="center" color="#000000" font-size="14" line-height="1.5" font-family="Helvetica Neue" class="italic">
                            <span class="italic">'.markdown($quote_text).'</span><span>  — '.$quote_author.'</span>
                            </mj-text>
                        </mj-column>
                    </mj-section>';
        
        return $string;
    }

    public function addMJMLBodyHeroImage ($image, $hero_text) {
        $string =   '<mj-section background-url="'. DEV_SITE_URL . $image .'" background-size="cover" background-repeat="no-repeat">
                        <mj-column width="600">
                            <mj-text align="center" color="#fff" font-size="40" line-height="1.25" padding-top="40" padding-bottom="40" font-family="Helvetica Neue">'.markdown($hero_text).'</mj-text>
                        </mj-column>
                    </mj-section>';
        
        return $string;
    }

    /* WAZ - need to add ability to parse markdown, apparently Statamic uses Parsedown but can't find file to include */
    public function addMJMLBodyTextBlock ($email_text) {
        $string =   '<mj-section background-color="#ffffff">
                        <mj-column width="600">
                            <mj-text align="left" color="#000000" font-size="14" line-height="1.25" font-family="Helvetica Neue">'.markdown($email_text).'</mj-text>
                        </mj-column>
                    </mj-section>';
        
        return $string;
    }

    public function addMJMLBodyTextAndImage ($text_and_image_header, $text_and_image_text, $text_and_image_image, $arrangement) {
        if (strlen($text_and_image_header) > 0) {
            $header = '<mj-text align="left" color="#000000" font-size="20" line-height="1.25" font-family="Helvetica Neue">'.$text_and_image_header.'</mj-text>';
        } else {
            $header = '';
        }
        switch ($arrangement) {
            case 'image-left':
                    $string =  '<mj-section background-color="#ffffff">
                                    <mj-column>
                                        <mj-image width="250" height="200" src="' . DEV_SITE_URL . $text_and_image_image . '" />
                                    </mj-column>
                                    <mj-column>
                                        '.$header.'
                                        <mj-text align="left" color="#000000" font-size="14" line-height="1.25" font-family="Helvetica Neue">'.markdown($text_and_image_text).'</mj-text>
                                    </mj-column>
                                </mj-section>';
                    break;
            case 'image-right':
                    if (strlen($text_and_image_header) > 0) {
                        $header = '<mj-text align="right" color="#000000" font-size="20" line-height="1.25" font-family="Helvetica Neue">'.$text_and_image_header.'</mj-text>';
                    } else {
                        $header = '';
                    }
                    $string =  '<mj-section background-color="#ffffff">
                                    <mj-column>
                                        '.$header.'
                                        <mj-text align="right" color="#000000" font-size="14" line-height="1.25" font-family="Helvetica Neue">'.markdown($text_and_image_text).'</mj-text>
                                    </mj-column>
                                    <mj-column>
                                        <mj-image width="250" height="200" src="' . DEV_SITE_URL . $text_and_image_image . '" />
                                    </mj-column>
                                </mj-section>';
                    break;
            case 'image-top':
                    $string =  '<mj-section background-color="#ffffff">
                                    <mj-image width="400" src="' . DEV_SITE_URL . $text_and_image_image . '" />
                                    '.$header.'
                                    <mj-text width="400" align="left" color="#000000" font-size="14" line-height="1.25" font-family="Helvetica Neue">'.markdown($text_and_image_text).'</mj-text>
                                </mj-section>';
                    break;
            case 'image-bottom':
                    $string =  '<mj-section background-color="#ffffff">
                                    '.$header.'
                                    <mj-text width="400" align="left" color="#000000" font-size="14" line-height="1.25" font-family="Helvetica Neue">'.markdown($text_and_image_text).'</mj-text>
                                    <mj-image width="400" src="' . DEV_SITE_URL . $text_and_image_image . '" />
                                </mj-section>';
                    break;
        }
        
        return $string;
    }

    public function addMJMLBodySignatureImage ($signature) {
        $string =   '<mj-section background-color="#ffffff">
                        <mj-column>
                            <mj-image width="200" align="left" src="' . DEV_SITE_URL . $signature . '" />
                        </mj-column>
                    </mj-section>';
        
        return $string;
    }

    public function addMJMLBodyTable ($table_field, $first_row_heading) {
        $string = '<mj-section background-color="#ffffff"><mj-column><mj-table>';
        for ($i=0; $i < count($table_field); $i++) {
            $cells = array($table_field[$i]['cells']);
            if (($i == 0) && ($first_row_heading == true)) {
                $string .= '<tr style="border-bottom:1px solid #ecedee;text-align:center;padding:15px;">';
            } else {
                $string .= '<tr style="text-align:center;padding:10px;">';
            }
            //$string .= json_encode($cells);
            foreach ($cells as $c) {
                /* $string .= json_encode($c) .'---'. $i; */
                /* $string .= '<td style="padding: 0 15px 0 0;">'.$c.'</td>'; */
                switch ($i) {
                    case 0:
                        foreach ($c as $t) {
                            if ($first_row_heading == true) {
                                $string .= '<th style="padding: 15px; text-align:center">'.$t.'</th>';
                            } else {
                                $string .= '<td style="padding: 10px; text-align:center">'.$t.'</td>';
                            }
                        }
                        break;
                    default:
                        foreach ($c as $t) {
                            $string .= '<td style="padding: 10px; text-align:center">'.$t.'</td>';
                        }
                        break;
                }
                /* if ($i == 0) {
                    $string .= '<th style="padding: 0 15px 0 0;">'.$c.'</th>';
                } else {
                    $string .= '<td style="padding: 0 15px 0 0;">'.$c.'</td>';
                } */
            }
            $string .= '</tr>';
        }
        $string .= '</mj-table></mj-column></mj-section>';
        
        return $string;
    }

    public function addMJMLBodyButton ($button_text, $button_link) {
        $string =   '<mj-section background-color="#ffffff">
                        <mj-column>
                            <mj-button href="' . $button_link . '" background-color="#A7885D" color="#ffffff" font-size="14" line-height="1.25" font-family="Helvetica Neue">'.$button_text.'</mj-button>
                        </mj-column>
                    </mj-section>';
        
        return $string;
    }

    public function addMJMLBodyDivider ($divider_text) {
        if (strlen($divider_text) < 1) {
            $divider_addon = '';
        } else {
            $divider_addon = '<mj-text font-size="14" line-height="1" padding="0" font-family="Helvetica Neue" align="center" color="#999999" letter-spacing="1px">'.strtoupper($divider_text).'</mj-text><mj-divider border-width="1px" border-style="dashed" border-color="lightgrey" />';
        }
        $string =   '<mj-section background-color="#ffffff">
                        <mj-column>
                            <mj-divider border-width="1px" border-style="dashed" border-color="lightgrey" />
                            '.$divider_addon.'
                        </mj-column>
                    </mj-section>';
        
        return $string;
    }

    public function addMJMLBodyScripture ($reference) {

        /* $url_ref = urlencode($reference);
        $ref = array(
            'q' => $url_ref
        );

        $ref = json_encode($ref);

        $ch = curl_init('https://api.esv.org/v3/passage/text/');
        // curl_setopt($ch, CURLOPT_URL, 'https://api.esv.org/v3/passage/text/');
        // curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        $headr = array();
        $headr[] = 'Content-type: application/json';
        $headr[] = 'Authorization: Token 28df28a3b3a2be32092d75d40101036209b1fc45';

        curl_setopt($ch, CURLOPT_HTTPHEADER, $headr);
        
        // curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_ANY);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $ref);
        $scripture_result=curl_exec($ch);
        $status_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);   //get status code
        curl_close($ch); */


        $key = 'fd9cfff133478926';
        $passage = urlencode($reference);
        $options = "include-passage-references=true&include-verse-numbers=false&include-headings=false&include-short-copyright=false&include-audio-link=false";
        $url = "http://www.esvapi.org/v2/rest/passageQuery?key=$key&passage=$passage&$options";
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        /* curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
        $headr = array();
        $headr[] = 'Accept: application/json';
        $headr[] = 'Authorization: Token 28df28a3b3a2be32092d75d40101036209b1fc45';
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headr); */
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
        //curl_setopt($ch, CURLOPT_POSTFIELDS, $passage);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        $scripture_result = curl_exec($ch);
        $status_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

       /*  $ch = curl_init();
        
        curl_setopt($ch, CURLOPT_URL, "https://api.esv.org/v3/passage/text/?q=jn11.35");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        //curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
        
        
        $headers = array();
        $headers[] = "Accept: application/json";
        $headers[] = "Authorization: Token 2338476f4eeffbbc9817f8265ef179b27f86d3ea";
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        
        $scripture_result = curl_exec($ch);
        if (curl_errno($ch)) {
            echo 'Error:' . curl_error($ch);
        }
        curl_close ($ch); */


        $string =  '<mj-section background-color="#ffffff">
                        <mj-column width="400">
                            <mj-text align="center" color="#000000" font-size="14" line-height="1.25" font-family="Helvetica Neue" font-style="italic">'.$scripture_result.'</mj-text>
                        </mj-column>
                    </mj-section>';

        return $string;
    }

    

    public function addMJMLFooter () {
        $string =   '<mj-section background-color="#3C6885">
                        <mj-column>
                            <mj-image padding-top="45" width="100" src=" ' . DEV_SITE_URL . '/assets/img/PCPC-vine-logo-white.svg" />
                            <mj-text align="center" color="#fff" font-size="12" line-height="1" padding-bottom="45" font-family="Helvetica Neue">
                                <p>Park Cities Presbyterian Church</p>
                                <p>4124 Oak Lawn Avenue</p>
                                <p>Dallas, TX 75219</p>
                                <a href="tel:2142242500" style="color:white; text-decoration:none;">214-224-2500</a>
                            </mj-text>
                        </mj-column>
                    </mj-section>';
        
        return $string;
    }

    public function addMJMLHeadInlineClasses ($classes) {
        $mjml_styles='';
        $unique_classes = array_unique($classes);
        foreach ($unique_classes as $c) {
            switch ($c) {
                case 'italic':
                    $mjml_styles .= '<mj-style inline="inline">';
                    $mjml_styles .=  '
                                    .italic {
                                        font-style: italic;
                                    }
                                   ';
                    $mjml_styles .= '</mj-style>';
                    break;
                case 'scripture':
                    $mjml_styles = '<mj-style inline="scripture">';
                    $mjml_styles .=  '
                                    .scripture {
                                        font-style: italic;
                                    }
                                ';
                    $mjml_styles .= '</mj-style>';
                    break;
            }
        }
        
        return $mjml_styles;
    }

    /* public function displayMarkdown ($text) {
        $Parsedown = new Parsedown();
        return $Parsedown->text($text);
    } */

    

}
