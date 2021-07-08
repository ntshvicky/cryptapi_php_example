<?php

error_reporting(E_ALL);
ini_set('display_errors', TRUE);
ini_set('display_startup_errors', TRUE);

?>

<!DOCTYPE html>
<html lang="en">
<head>
  <title>CryptAPI Example</title>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
   <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css">
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
  <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
</head>
<script>
    var i =0;
function checkstatus(a,b,c) {
    var xmlhttp = new XMLHttpRequest();
    xmlhttp.onreadystatechange = function() {
      if (this.readyState == 4 && this.status == 200) {
        var data = this.responseText;
        /*
        based on response , do your code here
        */
        document.getElementById("txtHint").innerHTML = data;
      }
    };
    xmlhttp.open("GET", "checklogs.php?coin="+a+"&invoice="+b+"&testapi="+c, true);
    xmlhttp.send();
}
</script>


<body>
<?php

$cryptapi_coin_options = array(
    'btc' => 'Bitcoin',
    'bch' => 'Bitcoin Cash',
    'ltc' => 'Litecoin',
    'eth' => 'Ethereum',
    'usdt' => 'USDT (ERC-20)',
    'usdc' => 'USDC (ERC-20)',
    'busd' => 'BUSD (ERC-20)',
    'pax' => 'PAX (ERC-20)',
    'tusd' => 'TUSD (ERC-20)',
    'bnb' => 'BNB (ERC-20)',
    'link' => 'ChainLink (ERC-20)',
    'cro' => 'Crypto Coin (ERC-20)',
    'mkr' => 'Maker (ERC-20)',
    'nexo' => 'NEXO (ERC-20)',
    'bcz' => 'BECAZ (ERC-20)',
    'xmr' => 'Monero',
    'iota' => 'IOTA',
    'trx' => 'TRX(TRC-20)',
);

$nounce = time();

if(isset($_POST["submit"])) {

    $coin = $_POST["cryptapi_selected_coin"]; //coin code which you want to transfer
    $email = $_POST["email"]; //E-mail address to receive payment notifications
    $invoice = isset($_POST["invoice"])?$_POST["invoice"]:"1234";
    $receiver_address = $_POST["receiver"]; // address of the receiver
    
    $callback_url = "http://youriphere/cryptotest/callback.php?invoice=".$invoice;//change this as per your callback url
    $parameters = ['order_id' => $invoice];
    $price = $_POST['amount'];

    $pending = 0; //Set this to 1 if you want to be notified of pending transactions (before they're confirmed) (default: False)
    $confirmations = 1; //Number of confirmations you want before receiving the callback (Min. 1) (default: 1)
    $post = 0; //you can change that to 1 if you want receive post requests
    $priority = 'fast'; //Set confirmation priority, needs to be one of ['fast', 'default', 'economic']
    

    $testapi = $_POST['testapi'];

    //step 1: convert usd to coin rate
    $curl = curl_init('https://api.cryptapi.io/'.$coin.'/info/');
    curl_setopt($curl, CURLOPT_TIMEOUT, 5);
    curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 5);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    $getrate = curl_exec($curl);
    $decoderate = json_decode($getrate);
    $rate = $decoderate->{'prices'}->{'USD'};
    $calculatedrate = round($price/$rate, 8);

    //step 2: generate payment address (address_in)
    //$addressin_req_url = 'https://sandbox.cryptapi.io/'.$coin.'/create/?address='.$receiver_address.'&callback='.$callback_url.'&pending='.$pending.'&confirmations='.$confirmations.'&post='.$post.'&email='.$email.'&priority='.$priority;
    $addressin_req_url = 'https://'.$testapi.'.cryptapi.io/'.$coin.'/create/?address='.$receiver_address.'&callback='.$callback_url.'&pending='.$pending.'&confirmations='.$confirmations.'&post='.$post.'&email='.$email.'&priority='.$priority;
    $curl = curl_init($addressin_req_url);
    curl_setopt($curl, CURLOPT_TIMEOUT, 5);
    curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 5);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    $getaddress = curl_exec($curl);
    $decodeaddress = json_decode($getaddress); 

  
    $getstatus = $decodeaddress->{'status'};
    if($getstatus == 'success'){
      $getaddressin = $decodeaddress->{'address_in'};
      echo '<img alt="qr code" src="https://chart.googleapis.com/chart?chs=150x150&cht=qr&chl=bitcoin:'. $getaddressin . '?amount=' . $calculatedrate . '"></img><br>';
      echo "Send <b>$cryptapi_coin_options[$coin] $calculatedrate</b> to <b>$getaddressin</b>";
      echo "<h5>Or Scan this QR Code from your Wallet </h5>";
      echo "<h5>In case of withdraw, send this QR code to Payer (Sender) </h5>";
      echo "<h3 id='demo'></h3>";
      echo '<br/><a style="cursor: pointer; color: \'blue\'" onclick="checkstatus(\''.$coin.'\', \''.$invoice.'\', \''.$testapi.'\')" >  <i class="fa fa-hand-o-right"></i> Click here to Check Payment Status</a><br/>';

      echo 'Result status of cryptapi response callback (here in data:{}): pending (transaction from user is not confirmed yet), received (received payment from user), sent (forwarded payment to you) and done (callback sent to your URL and received valid response [*ok*])';
    }

} else 
{ ?>

<div class="container">
  <h2>CryptAPI Example</h2>
  
  <form method="post">

    <div class="form-group">
      <label for="receiver">Receiver Wallet Address :</label>
      <input type="text" class="form-control" id="receiver" placeholder="Enter Wallet Address" name="receiver" value="">
    </div>

    <div class="form-group">
    <label for="email">Enter Email ID :<br/><small>(Where you want receive payment notification)</small></label>
    <input type="email" class="form-control" id="email" placeholder="Enter Your email" name="email" value="ntsh.vicky@gmail.com">
    </div>

    <div class="form-group">
    <label for="amount">Enter Amount (USD):</label>
    <input type="number" class="form-control" id="amount" placeholder="Enter amount in USD" name="amount" value="5">
    </div>


    <div class="form-group">
      <label for="invoice">Unique ID/Invoice No.:<br/><small>(Pass this as query string to make your callback url unique.)</small></label>
      <input type="text" class="form-control" id="invoice" name="invoice" value="<?=$nounce?>">
    </div>

    <div class="form-group">
      <label for="testapi">Test API:</label>
      <select name="testapi" class="form-control">
          <option value="sandbox">Testnet</option>
          <option value="api">Live</option>
      </select>
    </div>
    
    <div class="form-group form-check">

        <?php if( isset( $cryptapi_coin_options ) && is_array( $cryptapi_coin_options ) ) {
        foreach( $cryptapi_coin_options as $options => $value ) {
            ?>
            <div class="col-md-3">
            <div class="form-check form-check-inline">
                <input class="form-check-input" type="radio" name= "cryptapi_selected_coin" id="radio<?php echo $options ?>" value="<?php echo $options; ?>" />
                <label class="form-check-label" for="radio<?php echo $options ?>"><?php echo $value ?></label>
            </div>
            </div>
            <?php
        }
        } ?>
    
    </div>
    <button type="submit" name="submit" class="btn btn-primary">Submit</button>
  </form>
</div>


<?php }
?>


<span id="txtHint" style="color: red;"></span>


<?php 
if(isset($_POST["submit"])) {?>
<script>
// Set the date we're counting down to
 var countDownDate = new Date();

 countDownDate.setHours( countDownDate.getHours() + 1 );

// Update the count down every 1 second
var x = setInterval(function() {

  // Get today's date and time
  var now = new Date().getTime();

  // Find the distance between now and the count down date
  var distance = countDownDate - now;

  // Time calculations for days, hours, minutes and seconds
  var days = Math.floor(distance / (1000 * 60 * 60 * 24));
  var hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
  var minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
  var seconds = Math.floor((distance % (1000 * 60)) / 1000);

  // Display the result in the element with id="demo"
  document.getElementById("demo").innerHTML = days + "d " + hours + "h "
  + minutes + "m " + seconds + "s ";

  // If the count down is finished, write some text
  if (distance < 0) {
    clearInterval(x);
   
    document.getElementById("demo").innerHTML = "EXPIRED";
  } else {
     checkstatus('<?php echo $coin;?>','<?php echo $invoice;?>','<?php echo $testapi;?>');
  }
}, 1000);
</script>
<?php } ?>
</body>
</html>
