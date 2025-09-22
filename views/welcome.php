
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <link rel="canonical" href="https://faucet.tbwcjw.online/">
        <link rel="stylesheet" href="<?=$css?>">
        <meta name="robots" content="index, follow">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta name="title" content="tbwcjw faucet">
        <meta name="description" content="Free DuinoCoin every <?=substr(string: $cooldown, offset: 1)?>">
        <meta name="keywords" content="duino, duinocoin, arduino, crypto, faucet, blockchain, cryptocurrency, free, coins">
	<link rel="icon" type="image/png" sizes="32x32" href="../assets/ico/favicon-32x32.png">
	<link rel="icon" type="image/png" sizes="96x96" href="../assets/ico/favicon-96x96.png">
	<link rel="icon" type="image/png" sizes="16x16" href="../assets/ico/favicon-16x16.png">
	<meta name="msapplication-TileColor" content="#5965DB">
	<meta name="theme-color" content="#5965DB">
        <title>tbwcjw faucet</title>
        <script src="<?=$js?>"></script>  
    </head>
    <body>
    <? if(isset($status)): ?>
    <div class="overlay" id="overlay">
        <div class="message-box">
            <h1><?=$status?></h1>
	    <p>Join our <a href='https://discord.gg/T9FtuhHBGN'>discord</a>!</p>
            <button onclick="closeOverlay()">Close</button>
        </div>
    </div>
    <? endif; ?>
    <div class="container">
        <div class="faucet">
            <header>
                <h1>tbwcjw faucet</h1>
                <h2>Free DuinoCoin every <?=substr(string: $cooldown, offset: 1)?></h2>
            </header>
            <section>
                <form method="POST">
                    <input type="hidden" name="csrf_token" value="<?=$csrf_token?>">
                    <input type="text" name="wallet_address" placeholder="wallet address" required><br>
                    <img src="<?=$captcha_img?>" alt="Captcha"><br>
                    <input type="hidden" name="captcha_token" value="<?=$captcha_hash?>">
                    <input type="text" name="captcha_answer" placeholder="solve the captcha" required>
                    <input type="submit" value="Open the <?=$synonym?>">
                </form>
            </section>
        </div>
        <div class="health">
            <header>
                <h1>Statistics</h1>
            </header>
            <section>
                <p>Faucet Balance: <?=round(num: $statistics['faucet_balance'], precision: 2)?> ᕲ</p>
                <p>Payouts 24 Hour: <?=$statistics['transactions_24hour_count']?> (<?=$statistics['transactions_24hour_amount']?> ᕲ)</p>
                <p>Payouts All Time: <?=$statistics['transactions_alltime_count']?> (<?=$statistics['transactions_alltime_amount']?> ᕲ)</p>
                <p>Last updated: <?=$statistics['human_ts'] ?></p>
                <br>
                <p>Per use payout: <?=$transactionAmount?> ᕲ</p>
                <br>
                <p>Donate DuinoCoin to: <a href='https://explorer.duinocoin.com/?search=tbwcjw'>tbwcjw</a></p>
                <br>
                <p><a href='https://discord.gg/T9FtuhHBGN'>Discord</a> | <a href="terms.html">terms and conditions</a></p>
            </section>
        </div>
        <? if($statistics['donors'] !== null): ?>
        <div class="donors">
        <header>
            <h1>Thanks to our donors</h1>
        </header>
        <section>
            <p><?=$statistics['donors']?></p>
        </section>
        </div>
        <? endif; ?>
    </body>
    
</html>
