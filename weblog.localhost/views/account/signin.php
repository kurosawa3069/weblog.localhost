<?php $this->setPageTitle('title', 'サインイン') ?>
<h2>サインイン</h2>
<p>
  <a href="<?php print $base_url; ?>/account/signup">アカウントを作成</a>
</p>

<form action="<?php print $base_url; ?>/account/authenticate"
      method="post">
  <input type="hidden"
         name="_token"
         value="<?php print $this->escape($_token); ?>" />

  <?php if (isset($errors) && count($errors) > 0): ?>
    <?php print $this->render('errors', array('errors' => $errors)); ?>
  <?php endif; ?>

  <?php print $this->render('account/inputs', array(
      'user_name' => $user_name, 'password' => $password,
  )); ?>

  <p>
    <input type="submit"
           value="サインイン" />
  </p>
</form>