<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <link rel="canonical" href="https://faucet.tbwcjw.online/">
        <link rel="stylesheet" href="<?=$css?>">
        <meta name="robots" content="noindex, nofollow">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta name="title" content="tbwcjw faucet | error">
        <meta name="description" content="Free DuinoCoin every <?=substr(string: $cooldown, offset: 1)?>">
        <meta name="keywords" content="duino, duinocoin, arduino, crypto, faucet, blockchain, cryptocurrency, free, coins">
        <link rel="icon" type="image/png" sizes="32x32" href="../assets/ico/favicon-32x32.png">
        <link rel="icon" type="image/png" sizes="96x96" href="../assets/ico/favicon-96x96.png">
        <link rel="icon" type="image/png" sizes="16x16" href="../assets/ico/favicon-16x16.png">
        <meta name="msapplication-TileColor" content="#5965DB">
        <meta name="theme-color" content="#5965DB">
        <title>tbwcjw faucet | error</title>
        <script src="<?=$js?>"></script>  
    </head>
    <body>
    <div class="overlay" id="overlay">
        <div class="message-box">
            <? if($mode == 'debug'): ?>
                <code>
                    <h2><?=$error['message'] ?></h2>
                    <p><?=$error['timestamp'] ?></p>
                    <ul>
                    <? foreach($error['code'] as $line): ?>
                        <? if($line['highlight'] === true): ?>
                            <li class="highlight"><?=$line['line_num']?>. <?=$line['line'] ?></li>
                        <? else: ?>
                            <li><?=$line['line_num']?>. <?=$line['line'] ?></li>
                        <? endif; ?>
                    <? endforeach; ?>
                    </ul>
                    <pre>
                        <?=$error['trace']?>
                    </pre>
                </code>
            <? else: ?>
                <h1><?=$error['message']?></h1>
		<p>Need help? Join our <a href='https://discord.gg/T9FtuhHBGN'>discord</a></p>
            <? endif; ?>
            <form action="/" method="GET">
                <button>Try again</button>
            </form>
        </div>
    </div>
    </body>
    </html>
    
