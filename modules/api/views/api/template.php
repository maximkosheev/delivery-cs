<?php

/**
 *
 * @var integer $status
 * @var string $statusCodeMessage
 * @var string $message
 * @var string $signature
*/

    $this->title = $status . ' ' . $statusCodeMessage;
?>
<h1><?php echo $statusCodeMessage ?></h1>
<p><?php echo $message ?></p>
<hr />
<address><?php echo $signature; ?></address>
