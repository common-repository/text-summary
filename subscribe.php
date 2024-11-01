<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

function text_summary_subscribe(){

    echo'<h3>Subscribe <span class="display_price">£7.50</span> per month</h3>';
    echo'<p>Text Summary is a monthly subscription service to access the OpenAI gpt-4o model.</p>';
    echo'<form action="https://www.paypal.com/cgi-bin/webscr" method="post">'."\r\n";
    echo'<input name="cmd" type="hidden" value="_xclick-subscriptions">'."\r\n"; 
    echo'<input name="item_name" type="hidden" value="Text Summary Subscription">'."\r\n";
    echo' <input type="hidden" name="rm" value=2/>'."\r\n";
    echo'<input name="notify_url" type="hidden" value="https://www.aitextsummary.net/wp-admin/admin-ajax.php?action=text_summary_ipn">'."\r\n";
    echo' <input  type="hidden"  name="custom" value="'.esc_attr(site_url()).'" >'."\r\n";
    echo' <input name="business" type="hidden" value="clients@themoyles.co.uk">'."\r\n";
    echo' <input type="hidden" name="a3" class="price" value="7.50">'."\r\n";

    echo'<select name="currency_code" class="currency_code">'."\r\n";
    echo'<option value="GBP">Select Currency...</option>'."\r\n";
    echo'<option value="GBP">GBP £7.50</option>'."\r\n";
    echo'<option value="USD">USD $9.99</option>'."\r\n";
    echo'<option value="EUR">EUR 8.99</option>'."\r\n";
    echo'</select>'."\r\n";
    echo' <input type="hidden" class="ca-recurring"  name="p3" value="1" />'."\r\n";
    echo' <input type="hidden" class="ca-recurring" name="t3" value="M" />'."\r\n";
    echo' <input type="hidden" class="ca-recurring" name="src" value="1" />'."\r\n";
    echo' <input type="hidden" name="no_note" value=1>'."\r\n";
    echo' <input type="hidden" name="return" value="'.esc_attr(admin_url().'admin.php?page=text-summary').'">'."\r\n";
    
    echo '<button class="button-primary" type="submit">Pay monthly with PayPal</button></form></p>'."\r\n";
    echo '<script>
    jQuery(document).ready(function($){
        $("body").on("change",".currency_code",function(){
            var price=7.50;
            var currency_code = $(".currency_code").find(":selected").val();
            console.log(currency_code);
            switch(currency_code){
                case "GBP":
                    price = 7.50;
                    $(".display_price").text("£7.50");
                break;
                case "EUR":
                    price = 8.99;
                    $(".display_price").text("EUR 8.99");
                break;
                case "USD":
                    price = 9.99;
                    $(".display_price").text("$9.99");
                break;
            }
            $(".price").val(price);
        });
    });</script>';



}
