<?php
require '../vendor/autoload.php';

$ini = parse_ini_file('../monitoring.ini');
$ovh = new \Ovh\Api($ini['application_key'], $ini['application_secret'], $ini['endpoint'], $ini['consumer_key']);

$cache = '../cache/vps.json';
if (!file_exists($cache) || filemtime($cache) < (time() - 7 * 24 * 60 * 60) || isset($_GET['nocache'])) {
  $json = array();

  $vps = $ovh->get('/vps');
  foreach ($vps as $v) {
    $_v = $ovh->get('/vps/'.$v);
    $_v['distribution'] = $ovh->get('/vps/'.$v.'/distribution');
    $_v['ipAddresses'] = $ovh->get('/vps/'.$v.'/ips');
    $_v['infos'] = $ovh->get('/vps/'.$v.'/serviceInfos');

    $json[] = $_v;
  }

  if (!file_exists('../cache') || !is_dir('../cache')) { mkdir('../cache'); }
  file_put_contents($cache, json_encode($json, JSON_PRETTY_PRINT));
}
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>OVH VPS Monitoring</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-alpha.6/css/bootstrap.min.css" integrity="sha384-rwoIResjU2yc3z8GV/NPeZWAv56rSmLldC3R/AZzGRnGxQQKnKkoFVhFQhNUwEyJ" crossorigin="anonymous">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css" integrity="sha384-wvfXpqpZZVQGK6TAh5PVlGOfQNHSoD2xbE+QkPxCAFlNEevoEH3Sl0sibVcOQVnN" crossorigin="anonymous">
    <link rel="stylesheet" href="style.css">
  </head>
  <body>
    <div class="container-fluid">
      <h1 class="mt-3">OVH Monitoring</h1>
      <ul class="nav nav-tabs mt-3">
        <li class="nav-item">
          <a class="nav-link active" href="vps.php">VPS</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="cloud.php">Cloud</a>
        </li>
      </ul>
      <table class="table table-bordered table-striped table-sm mt-3">
        <thead class="thead-inverse">
          <tr>
            <th colspan="2">VPS</th>
            <th class="text-center"><i class="fa fa-bell" aria-hidden="true"></i></th>
            <th>IP</th>
            <th>Zone</th>
            <th>Offer</th>
            <th colspan="2">OS</th>
            <th colspan="3">Disk(s)</th>
            <th colspan="3">vCore(s)</th>
            <th colspan="3">RAM</th>
            <th>Status</th>
          </tr>
        </thead>
        <tbody>
<?php
$vps = json_decode(file_get_contents($cache));
foreach ($vps as $v) {
  $d1 = new DateTime($v->infos->expiration);
  $d2 = new DateTime();
  $diff = $d1->diff($d2);
  $expiration = ($diff->days <= 30);
?>
          <tr data-vps="<?= $v->name ?>">
            <th class="text-nowrap">
<?php if ($expiration === TRUE && $v->infos->renewalType === 'manual') { ?>
              <span class="text-warning" title="<?= sprintf(_('Expiration in %d days'), $diff->days) ?>" style="cursor: help;">
<?php } ?>
              <?= $v->name ?><br>
              <small><?= $v->displayName ?></small>
<?php if ($expiration === TRUE && $v->infos->renewalType === 'manual') { ?>
              </span>
<?php } ?>
            </th>
            <td class="text-center"><a href="#modal-info" data-toggle="modal"><i class="fa fa-info-circle" aria-hidden="true"></i></a></td>
            <td class="text-center alert-live"><i class="fa fa fa-spinner fa-pulse fa-fw"></i></td>
            <td>
              <ul class="list-unstyled mb-0">
<?php foreach ($v->ipAddresses as $ip) { ?>
                <li><?= $ip ?></li>
<?php } ?>
              </ul>
            </td>
            <td style="text-nowrap"><?= $v->zone ?></td>
            <td class="text-nowrap"><?= $v->model->offer ?><br><em class="small"><?= $v->model->version ?> - <?= $v->model->name ?></em></td>
            <td style="vertical-align: middle;"><?= $v->distribution->name ?></td>
            <td style="vertical-align: middle;" class="text-nowrap"><?= $v->distribution->bitFormat ?> bits</td>
            <td style="vertical-align: middle;" class="text-nowrap text-right"><?= $v->model->disk ?> Go</td>
            <td style="vertical-align: middle;" class="text-nowrap text-right disk-live"><i class="fa fa fa-spinner fa-pulse fa-fw"></i></td>
            <td style="vertical-align: middle;" class="text-nowrap text-center"><a href="#disk-chart" style="text-decoration: none;"><i class="fa fa-line-chart" aria-hidden="true"></i></a></td>
            <td style="vertical-align: middle;" class="text-right"><?= $v->vcore ?></td>
            <td style="vertical-align: middle;" class="text-nowrap text-right cpu-live"><i class="fa fa fa-spinner fa-pulse fa-fw"></i></td>
            <td style="vertical-align: middle;" class="text-nowrap text-center"><a href="#cpu-chart" style="text-decoration: none;"><i class="fa fa-line-chart" aria-hidden="true"></i></a></td>
            <td style="vertical-align: middle;" class="text-nowrap text-right"><?= ($v->memoryLimit / 1024) ?> Go</td>
            <td style="vertical-align: middle;" class="text-nowrap text-right ram-live"><i class="fa fa fa-spinner fa-pulse fa-fw"></i></td>
            <td style="vertical-align: middle;" class="text-nowrap text-center"><a href="#ram-chart" style="text-decoration: none;"><i class="fa fa-line-chart" aria-hidden="true"></i></a></td>
            <td style="vertical-align: middle;" class="text-nowrap">
              <span class="badge badge-default status-ping">ping</span>
              <span class="badge badge-default status-ssh">ssh</span>
              <span class="badge badge-default status-dns">dns</span>
              <span class="badge badge-default status-http">http</span>
              <span class="badge badge-default status-https">https</span>
              <span class="badge badge-default status-smtp">smtp</span>
              <span class="badge badge-default status-tools">tools</span>
            </td>
          </tr>
<?php
}
?>
        </tbody>
        <tfoot>
          <tr>
            <td colspan="18" class="text-right small text-muted">
              <?= _('Last update') ?> : <?= date('d.m.Y H:i', filemtime($cache)) ?>
              <a id="refresh" href="vps.php?nocache"><i class="fa fa-refresh" aria-hidden="true"></i> Refresh</a>
            </td>
          </tr>
        </tfoot>
      </table>

      <div id="console" class="text-danger small">
        <ol></ol>
      </div>
    </div>

    <div id="modal-alert" class="modal fade">
      <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title"></h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <div class="modal-body">
            <table class="table table-striped table-sm small">
              <thead>
                <tr>
                  <th>Time</th>
                  <th>Reference</th>
                  <th>Description</th>
                  <th>Status</th>
                  <th>Type</th>
                  <th>Impact</th>
                </tr>
              </thead>
              <tbody>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>

    <div id="modal-chart" class="modal fade">
      <div class="modal-dialog" role="document">
        <div class="modal-content">
          <div class="modal-body">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
            <canvas id="chart" width="468" height="400"></canvas>
          </div>
        </div>
      </div>
    </div>

    <div id="modal-info" class="modal fade">
      <div class="modal-dialog" role="document">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title"></h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <div class="modal-body"></div>
        </div>
      </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.1.1.min.js" integrity="sha256-hVVnYaiADRTO2PzUGmuLJr8BLUSjGIZsDYGmIJLv2b8=" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/tether/1.4.0/js/tether.min.js" integrity="sha384-DztdAPBWPRXSA/3eYEEUWrWCy7G5KFbe8fFjk5JAIxUYHKkDx6Qin1DkWx51bBrb" crossorigin="anonymous"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-alpha.6/js/bootstrap.min.js" integrity="sha384-vBWWzlZJ8ea9aCX4pEW3rVHjgjt7zpkNpZk+02D9phzyeVkE+jo0ieGizqPLForn" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.5.0/Chart.bundle.js" integrity="sha256-jYMHiFJgIHHSIyPp1uwI5iv5dYgQZIxaQ4RwnpEeEDQ=" crossorigin="anonymous"></script>
    <script src="vps.js"></script>
  </body>
</html>
