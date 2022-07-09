<?php
get_header();

$canonical_url = wp_get_canonical_url();

// Sets the decoded URL as the HTML title (emulates the behavior of displaying a
// text file in a web browser).
$html_title = rawurldecode($canonical_url); ?>
    <title><?php echo $html_title; ?></title>
    <link rel="canonical" href="<?php echo $canonical_url; ?>">
    <link rel="license" href="https://creativecommons.org/licenses/by-sa/4.0/">
    <style>
      pre {
        word-wrap: break-word;
        overflow-wrap: break-word;
        white-space: -moz-pre-wrap;
        white-space: -pre-wrap;
        white-space: -o-pre-wrap;
        white-space: pre-wrap;
        white-space: break-spaces;
      }
    </style><?php
// Outputs the structured data.
while (have_posts()) {
  the_post();

  // Uses the decoded slug as the post title.
  $title = rawurldecode($post->post_name);

  // Gets the publication and modification dates of the post in UTC in ISO 8601
  // format.  We should not use the local time returned by `get_the_date()`,
  // `get_the_time()`, or `get_post_time($gmt = false)`.  They will return
  // incorrect dates for existing posts if the user changes the Timezone setting
  // in WP Admin > Settings > General.  They refer to `$post->post_date`, which
  // does not contain time zone information, even though the date is based on
  // the Timezone setting when the post was originally published.  Therefore,
  // any code that simply uses it is broken.  The same goes for
  // `get_the_modified_date()`, `get_the_modified_time()`, and
  // `get_post_modified_time($gmt = false)`.
  $published_date = get_post_time('c', true);
  $modified_date = get_post_modified_time('c', true);

  // Gets the raw post content here for later output to the HTML body.
  $escaped_content = htmlspecialchars(
    rtrim($post->post_content),
    ENT_NOQUOTES,
  ); ?>

    <script type="application/ld+json">
      {
        "@context": "https://schema.org",
        "@type": "BlogPosting",
        "headline": "<?php echo $title; ?>",
        "datePublished": "<?php echo $published_date; ?>",
        "dateModified": "<?php echo $modified_date; ?>"
      }
    </script><?php
} ?>

  </head>
  <body>
    <main>
      <article>
        <pre><?php echo $escaped_content; ?>

</pre>
      </article>
    </main>
<?php get_footer(); ?>
