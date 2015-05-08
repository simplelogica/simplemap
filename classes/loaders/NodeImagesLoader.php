<?php


class NodeImagesLoader {


  /**
   * Loads all nodes that have associated images.
   * @return Array of nodes
   */
  function loadNodesWithImages() {
    $query = db_select('file_usage', 'u')
      ->distinct()
      ->fields('u', ['id'])
      ->execute();
    // We can omit the distinct() call since we are using a fetchAllAssoc call
    // based on the 'id' field.  Results with the same 'id' will override the
    // existing ones, so we will never get repeated 'id's.
    $nids = array_keys($query->fetchAllAssoc('id'));
    return node_load_multiple($nids);
  }


  /**
   * Loads all the images for the given node NID.
   * @return Array of the node images.  Each image contains a 'fid' and a 'uri'.
   */
  function loadImagesFromNode($nid) {
    $query = db_select('file_managed', 'f');
    $query->join('file_usage', 'u', 'f.fid = u.fid');

    $query->fields('f', ['fid', 'uri'])
      ->condition('f.filemime', 'image/%', 'LIKE')
      ->condition('u.id', $nid, '=');
    return $query->execute();
  }

}