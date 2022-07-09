<?php
get_header();

// The home URL specified in WP Admin > Settings > General > Site Address (URL)
// (with "/" appended to the end).
$home_url_with_slash = home_url('/'); ?>
    <title><?php bloginfo('name'); ?></title>
    <link rel="canonical" href="<?php echo $home_url_with_slash; ?>">
    <link rel="license" href="https://creativecommons.org/licenses/by-sa/4.0/">
  </head>
  <body>
    <main>
      <ul><?php
// Outputs the list of posts.
while (have_posts()) {
  the_post();

  // Makes the post URL relative to the home URL.
  $url = str_replace($home_url_with_slash, '', get_permalink());

  // Uses the decoded slug as the post title.
  $title = rawurldecode($post->post_name); ?>

        <li>
          <a href="<?php echo $url; ?>"><?php echo $title; ?></a>
        </li><?php
} ?>

      </ul>
    </main>
<?php get_footer(); ?>
