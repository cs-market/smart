<?php

function fn_exim_smart_distribution_import_images($prefix, $image_file, $detailed_file, $position, $type, $object_id, $object, $import_options = null)
{
  if ($detailed_file && strpos($detailed_file, '://') !== false) {
    $extensions = [
      'jpg', 'jpeg', 'png', 'bmp'
    ];


    $img_url = false;
    foreach ($extensions as $extension) {
      if (stripos($detailed_file, $extensions) !== false) {
        $img_url = true;
        break;
      }
    }

    if (!$img_url) {
      //  if return <img />, get src
      $img_tag = fn_get_contents($detailed_file);
      preg_match('/< *img[^>]*src *= *["\']?([^"\']*)/i', $img_tag, $matches);
      if (isset($matches[1])) {
        $detailed_file = $matches[1];
      }
    }
  }

  fn_exim_import_images($prefix, $image_file, $detailed_file, $position, $type, $object_id, $object, $import_options);
}
