<?php

final class PhabricatorProjectsEditEngineExtension
  extends PhabricatorEditEngineExtension {

  const EXTENSIONKEY = 'projects.projects';

  public function getExtensionPriority() {
    return 500;
  }

  public function isExtensionEnabled() {
    return PhabricatorApplication::isClassInstalled(
      'PhabricatorProjectApplication');
  }

  public function getExtensionName() {
    return pht('Projects');
  }

  public function supportsObject(
    PhabricatorEditEngine $engine,
    PhabricatorApplicationTransactionInterface $object) {

    return ($object instanceof PhabricatorProjectInterface);
  }

  public function buildCustomEditFields(
    PhabricatorEditEngine $engine,
    PhabricatorApplicationTransactionInterface $object) {

    $edge_type = PhabricatorTransactions::TYPE_EDGE;
    $project_edge_type = PhabricatorProjectObjectHasProjectEdgeType::EDGECONST;

    $object_phid = $object->getPHID();
    if ($object_phid) {
      $project_phids = PhabricatorEdgeQuery::loadDestinationPHIDs(
        $object_phid,
        $project_edge_type);
      $project_phids = array_reverse($project_phids);
    } else {
      $project_phids = array();
    }

    $projects_field = id(new PhabricatorProjectsEditField())
      ->setKey('projectPHIDs')
      ->setLabel(pht('Projects'))
      ->setEditTypeKey('projects')
      ->setAliases(array('project', 'projects'))
      ->setIsCopyable(true)
      ->setUseEdgeTransactions(true)
      ->setCommentActionLabel(pht('Change Projects'))
      ->setDescription(pht('Select projects for the object.'))
      ->setTransactionType($edge_type)
      ->setMetadataValue('edge:type', $project_edge_type)
      ->setValue($project_phids);

    $projects_field->setViewer($engine->getViewer());

    $edit_add = $projects_field->getConduitEditType('projects.add')
      ->setConduitDescription(pht('Add projects.'));

    $edit_set = $projects_field->getConduitEditType('projects.set')
      ->setConduitDescription(
        pht('Set projects, overwriting current value.'));

    $edit_rem = $projects_field->getConduitEditType('projects.remove')
      ->setConduitDescription(pht('Remove projects.'));

    return array(
      $projects_field,
    );
  }

}
