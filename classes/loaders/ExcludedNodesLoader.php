<?php


class ExcludedNodesLoader {


  function loadExcludedNids() {
    $query = db_select('simplemap_exclusions', 'e')
      ->fields('e', ['nid'])
      ->execute();
    return array_keys($query->fetchAllAssoc('nid'));
  }

  function loadExcludedNodes() {
    return node_load_multiple($this->loadExcludedNids());
  }
}