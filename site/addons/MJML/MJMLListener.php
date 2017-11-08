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
      define("DEV_SITE_URL", 'http://pcpcemail.dev');

      $blocks = $entry->get('content_blocks');

      $block_count = count($blocks);

      $block_types = array();

      for ($i = 0; $i < $block_count; $i++) {
          $entry->set('block-'.$i.'-type', $blocks[$i]['type']);
          array_push($block_types, $blocks[$i]['type']);
      }

      $mjml_code = '<mjml>';
      /* will need to determine how to add head inline-style classes only once, only as needed, probably cycle through all distinct block types */
      $mjml_head = '<mj-head>';
      $mjml_body = '<mj-body><mj-container>';

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
                $mjml_body .= $this->addMJMLBodyTextAndImage($blocks[$i]['text_and_image_text'], $blocks[$i]['text_and_image_image'], $blocks[$i]['arrangement']);
                break;
            case 'signature_image':
                $mjml_body .= $this->addMJMLBodySignatureImage($blocks[$i]['signature']);
                break;
            case 'table':
                $mjml_body .= $this->addMJMLBodyTable($blocks[$i]['table_field']);
                break;
        }
      }

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


/*       block-0-type: quote
      block-1-type: hero_image
      block-2-type: text_block
      block-3-type: text_and_image
      block-4-type: signature_image
      block-5-type: table
      block-6-type: button
      block-7-type: divider */



      // CURL Request
      
/*       $mjml_app_id='INSERT MJML APP ID HERE';
      $mjml_sk='INSERT MJML SK HERE';
      $url='https://api.mjml.io/v1/render';
      // may need double quotes around $userpwd
      $userpwd = $mjml_app_id.':'.$mjml_sk;
      
      $ch = curl_init();
      curl_setopt($ch, CURLOPT_URL, $url);
      // curl_setopt($ch, CURLOPT_TIMEOUT, 30);
      // curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
      // curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_ANY);
      curl_setopt($ch, CURLOPT_USERPWD, $userpwd);
      curl_setopt($ch, CURLOPT_POSTFIELDS, $mjml_code);
      $mjml_result=curl_exec($ch);
      $status_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);   //get status code
      curl_close($ch);

      // End CURL Request

      $entry->set('mjml_result', $mjml_result); */

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
                            <mj-text align="center" color="#000000" font-size="12" line-height="1.5" font-family="Helvetica Neue" class="italic">'
                                .$quote_text.'<span>— '.$quote_author.'</span>
                            </mj-text>
                        </mj-column>
                    </mj-section>';
        
        return $string;
    }

    public function addMJMLBodyHeroImage ($image, $hero_text) {
        $string =   '<mj-section background-url="'. DEV_SITE_URL . $image .'" background-size="cover" background-repeat="no-repeat">
                        <mj-column width="600">
                            <mj-text align="center" color="#fff" font-size="40" line-height="1.25" padding-top="40" padding-bottom="40" font-family="Helvetica Neue">'.$hero_text.'</mj-text>
                        </mj-column>
                    </mj-section>';
        
        return $string;
    }

    /* WAZ - need to add ability to parse markdown, apparently Statamic uses Parsedown but can't find file to include */
    public function addMJMLBodyTextBlock ($email_text) {
        $string =   '<mj-section background-color="#ffffff">
                        <mj-column width="600">
                            <mj-text align="left" color="#000000" font-size="12" line-height="1.25" font-family="Helvetica Neue">'.$email_text.'</mj-text>
                        </mj-column>
                    </mj-section>';
        
        return $string;
    }

    public function addMJMLBodyTextAndImage ($text_and_image_text, $text_and_image_image, $arrangement) {
        switch ($arrangement) {
            case 'image-left':
                    $string =  '<mj-section background-color="#ffffff">
                                    <mj-column>
                                        <mj-image width="200" src="' . DEV_SITE_URL . $text_and_image_image . '" />
                                    </mj-column>
                                    <mj-column>
                                        <mj-text align="left" color="#000000" font-size="12" line-height="1.25" font-family="Helvetica Neue">'.$text_and_image_text.'</mj-text>
                                    </mj-column>
                                </mj-section>';
                    break;
            case 'image-right':
                    $string =  '<mj-section background-color="#ffffff">
                                    <mj-column>
                                        <mj-text align="left" color="#000000" font-size="12" line-height="1.25" font-family="Helvetica Neue">'.$text_and_image_text.'</mj-text>
                                    </mj-column>
                                    <mj-column>
                                        <mj-image width="200" src="' . DEV_SITE_URL . $text_and_image_image . '" />
                                    </mj-column>
                                </mj-section>';
                    break;
            case 'image-top':
                    $string =  '<mj-section background-color="#ffffff">
                                    <mj-image width="400" src="' . DEV_SITE_URL . $text_and_image_image . '" />
                                    <mj-text width="400" align="left" color="#000000" font-size="12" line-height="1.25" font-family="Helvetica Neue">'.$text_and_image_text.'</mj-text>
                                </mj-section>';
                    break;
            case 'image-bottom':
                    $string =  '<mj-section background-color="#ffffff">
                                    <mj-text width="400" align="left" color="#000000" font-size="12" line-height="1.25" font-family="Helvetica Neue">'.$text_and_image_text.'</mj-text>
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

    public function addMJMLBodyTable ($table_field) {
        $string = '<mj-section background-color="#ffffff"><mj-column><mj-table>';
        for ($i=0; $i < count($table_field); $i++) {
            $cells = array($table_field['cells'][$i]);
            if ($i == 0) {
                $string .= '<tr style="border-bottom:1px solid #ecedee;text-align:left;padding:15px 0;">';
            }
            //$string .= json_encode($cells);
            foreach ($cells as $c) {
                $string .= json_encode($c) .'---'. $i;
                /* $string .= '<td style="padding: 0 15px 0 0;">'.$c.'</td>'; */
                /* switch ($i) {
                    case 0:
                        $string .= '<th style="padding: 0 15px 0 0;">'.$c.'</th>';
                        break;
                    default:
                        $string .= '<td style="padding: 0 15px 0 0;">'.$c.'</td>';
                        break;
                } */
                /* if ($i == 0) {
                    $string .= '<th style="padding: 0 15px 0 0;">'.$c.'</th>';
                } else {
                    $string .= '<td style="padding: 0 15px 0 0;">'.$c.'</td>';
                } */
            }
            if ((count($table_field) - $i) == 1) {
                $string .= '</tr>';
            }
        }
        $string .= '</mj-table></mj-column></mj-section>';
        
        return $string;
    }

    public function addMJMLHeadInlineClasses ($classes) {
        $unique_classes = array_unique($classes);
        $mjml_styles = '<mj-style inline="inline">';
        foreach ($unique_classes as $c) {
            switch ($c) {
                case 'italic':
                    $mjml_styles .=  '
                                    .italic {
                                        font-style: italic;
                                    }
                                   ';
                    break;
            }
        }
        $mjml_styles .= '</mj-style>';
        
        return $mjml_styles;
    }

    /* public function displayMarkdown ($text) {
        $Parsedown = new Parsedown();
        return $Parsedown->text($text);
    } */

    

}
