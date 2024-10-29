<?php 

if (isset($_SERVER['HTTPS']) &&
    ($_SERVER['HTTPS'] == 'on' || $_SERVER['HTTPS'] == 1) ||
    isset($_SERVER['HTTP_X_FORWARDED_PROTO']) &&
    $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https') {
  $protocol = 'https://';
}
else {
  $protocol = 'http://';
}

?>

<h2>Nine Dots Player</h2>
<section id="admin-nineditsplayer">
	<div id="admin-nineditsplayer-form">
		<b>You can select the parameter to automatically connect your player to all blog articles.</b>
		<form >
			<?php $options = get_option( 'add_ninedotsplayer' );?>
			<input type="checkbox" id="check" name="check"  <?php echo ($options == 1 ? 'checked':''); ?>>
			<label for="check">Add player for all posts automatically</label><br>
			<button id="addPlayer" type="button">Save</button>
		</form>	
	</div>
	<div id="admin-nineditsplayer-copy">
		<b>To enable the audio conversion of your articles please contact AudioDots and provide the AudioDots API URL of your WordPress site.</b>
		<div>
			
			<input type="" name="" id="copy-url-data" value="<?php echo $protocol.$_SERVER['SERVER_NAME'];?>/wp-json/api/json/GetLastToArticle"/><br>
			<a class="sendUrl" href="mailto:itay@audiodots.com?subject=New WordPress site registration requested&body=API URL is: <?php echo $protocol.$_SERVER['SERVER_NAME'];?>/wp-json/api/json/GetLastToArticle"><label id="copy-url" >Send the URL</label></a>
		</div>
	</div>
</section>

<script type="text/javascript">
jQuery('#addPlayer').click( function () {

    var check = jQuery('#check').is(":checked") ? 1:0; 
    jQuery.ajax({
        url: ajaxurl,
        type: "POST",
        data: {
            'action': 'adp_set_player',
            'check': check,

        },
        success: function (data) {
            alert('Updated');
        }
    });

});

</script>

<style type="text/css">
	section#admin-nineditsplayer > div {
	    padding: 10px 0 20px 0;
	}

	a.sendUrl {
	    text-decoration: none;
	}

	.sendUrl:focus{
		color: unset;
    	box-shadow: unset;
    	outline: unset;	
	}

	
	#admin-nineditsplayer-form input {
	    margin: -3px 7px 0px 0px;
	}

	#admin-nineditsplayer-form form {
	    padding-top: 10px;
	}

	#admin-nineditsplayer-form button {
	    margin: 8px 0 0 0px;
	    padding: 10px 20px;
	    background: #3796c6;
	    border: unset;
	    color: white;
	    font-weight: bold;
	    text-transform: uppercase;
	    border-radius: 3px;
	    cursor: pointer;
	}

	#admin-nineditsplayer-copy > div {
	    padding: 10px 0 50px 0;
	}

	input#copy-url-data {
	    width: 100%;
	    max-width: 500px;
	    height: 35px;
	    padding: 0 0 0 12px;
	    color: #535353;
	    margin-bottom: 20px;
	}

	label#copy-url {
	    padding: 10px 20px;
	    background: #3796c6;
	    border: unset;
	    color: white;
	    font-weight: bold;
	    text-transform: uppercase;
	    border-radius: 3px;
	    cursor: pointer;
	}
</style>


<?php 
die;
?>
