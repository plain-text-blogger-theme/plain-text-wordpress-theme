<?php
get_header();
while (have_posts()) : the_post();
?>
<pre><?php
  echo str_replace(['&', '<'], ['&amp;', '&lt;'], get_post()->post_content);
?></pre>
<?php endwhile; ?>
