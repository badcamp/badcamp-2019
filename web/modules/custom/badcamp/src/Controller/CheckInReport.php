<?php

namespace Drupal\badcamp\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Database\Connection;

/**
 * Class CheckInReportController.
 */
class CheckInReport extends ControllerBase {

  /**
   * Return the Report for Check Ins.
   **/
  public function report() {
    $query = \Drupal::database()->query("SELECT DATE_FORMAT(FROM_UNIXTIME(flag.created), '%Y-%m-%d') AS 'date', COUNT(*) as 'total' FROM {users_field_data} ufd LEFT JOIN {flagging} flag ON (flag.flag_id='attendance' AND flag.entity_id = ufd.uid) AND ufd.uid > 0 GROUP BY DATE_FORMAT(FROM_UNIXTIME(flag.created), '%Y-%m-%d')");
    $results = $query->fetchAll();
    $rows = [];
    foreach ($results as $result) {
      $rows[] = [
        ($result->date == '' ? $this->t('Not Checked In') : $result->date),
        $result->total,
      ];
    }
    return [
      'report' => [
        '#theme' => 'table',
        '#rows' => $rows,
        '#headers' => ['Date', 'Number']
      ]
    ];
  }
}

