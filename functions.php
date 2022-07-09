<?php
// Finds an invalid character in the post title and returns the character and
// its position.  The title must consist only of hyphens, numbers, and lowercase
// letters.
function match_invalid_title($title) {
  preg_match(
    '/[^-0123456789abcdefghijklmnopqrstuvwxyz]/',
    $title,
    $matches,
    PREG_OFFSET_CAPTURE,
  );
  return $matches ? $matches[0] : $matches;
}

// Finds an invalid character in the post content and returns the character and
// its position.  The content must consist only of ASCII printable characters
// (including spaces), horizontal tabs, and newlines.
function match_invalid_content($content) {
  preg_match('/[^\t\r\n\x20-\x7E]/', $content, $matches, PREG_OFFSET_CAPTURE);
  return $matches ? $matches[0] : $matches;
}

// Sets the post status of the post data to "draft" and adds the error message
// metadata to the current user.
function convert_to_draft_with_error(&$data, $message) {
  static $error_key = 'invalid_post_data_error';
  add_user_meta(get_current_user_id(), $error_key, $message);

  $data['post_status'] = 'draft';
  return $data;
}

// Outputs the error message on the post editing screen.
function show_invalid_post_data_error() {
  static $error_key = 'invalid_post_data_error';
  $user_id = get_current_user_id();
  $message = get_user_meta($user_id, $error_key, true);
  if ($message) {
    delete_user_meta($user_id, $error_key); ?>
    <div class="notice notice-error is-dismissible">
      <p><?php echo htmlspecialchars($message, ENT_NOQUOTES); ?></p>
    </div><?php
  }
}
add_filter('admin_notices', 'show_invalid_post_data_error');

// Validates the post data before it is published.
function validate_post_data($data) {
  if ($data['post_status'] !== 'publish') {
    return $data;
  }

  $title = wp_unslash($data['post_title']);
  $slug = wp_unslash(rawurldecode($data['post_name']));
  $content = wp_unslash($data['post_content']);

  // Rejects the post if the title and slug are not the same.
  if ($title !== $slug) {
    return convert_to_draft_with_error($data, 'Mismatched title and slug.');
  }

  // Rejects the post if the slug starts with "wp-", or if a file or directory
  // with the same name as the slug exists in the WP installation directory.
  // This prevents confusion with wp-admin, wp-json, etc.
  if (
    preg_match('/\Awp-/', $slug) ||
    file_exists(get_home_path() . DIRECTORY_SEPARATOR . $slug)
  ) {
    return convert_to_draft_with_error($data, 'Forbidden slug.');
  }

  // Rejects the post if the content is empty or consists only of whitespace.
  if ($content === '' || ctype_space($content)) {
    return convert_to_draft_with_error($data, 'Empty content.');
  }
  
  // Rejects the post if the title contains an invalid character.
  $matches = match_invalid_title($title);
  if ($matches) {
    return convert_to_draft_with_error(
      $data,
      sprintf(
        'Invalid character 0x%02X in title at position %d.',
        ord($matches[0]),
        $matches[1] + 1,
      ),
    );
  }

  // Rejects the post if the content contains an invalid character.
  $matches = match_invalid_content($content);
  if ($matches) {
    // Finds the line and column numbers of the invalid character.
    $pre_match = substr($content, 0, $matches[1]);

    $line_number = substr_count($pre_match, "\n") + 1;

    $prev_line_end_pos = strrpos($pre_match, "\n");
    if ($prev_line_end_pos === false) {
      $prev_line_end_pos = -1;
    }
    $column_number = strlen($pre_match) - $prev_line_end_pos;

    return convert_to_draft_with_error(
      $data,
      sprintf(
        'Invalid character 0x%02X in content at line %d, column %d.',
        ord($matches[0]),
        $line_number,
        $column_number,
      ),
    );
  }

  return $data;
}
add_filter('wp_insert_post_data', 'validate_post_data');

// Remove favicon.
function remove_favicon() {
  global $wp_query;
  $wp_query->set_404();
  status_header(404);
  get_template_part('404');
  exit;
}
add_action('do_faviconico', 'remove_favicon'); ?>
