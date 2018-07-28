<?php $this->setPageTitle('title', $status['user_name']) ?>

<?php print $this->render('blog/status', array('status' => $status)); ?>