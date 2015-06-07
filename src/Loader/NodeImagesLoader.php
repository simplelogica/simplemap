<?php


class NodeImagesLoader {


  /**
   * Loads all nodes that have associated images.
   * @return Array of nodes
   */
  function loadNodesWithImages() {
    $query = db_select('file_usage', 'u');
    $query->join('node', 'n', 'u.id = n.nid');
    
    $query->fields('u', ['id'])
      ->condition('n.status', NODE_PUBLISHED);
    $result = $query->execute();
    $nids = array_keys($result->fetchAllAssoc('id'));
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
