<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php
// make sure $show_done always exists
$show_done = isset($show_done) && $show_done;
$doneOrdersCount = $doneOrdersCount ?? 0;
$totalOrdersCount = $totalOrdersCount ?? 0;
$progressPercent = $progressPercent ?? 0;
?>

<section class="resume-section p-3" style="align-items : normal !important" id="about">
    <div class="resume-section-content">
        <div class="row">
            <div class="col-sm-6">
                <h3 class="mb-1">INPUT BARANG KE CONTAINER</h3>
                <h5 class="m-0">
                    <div id="jam" style="text-align: left;" class="font-bold text-secondary">-- : -- : --</div>
                </h5>
            </div>
        </div>
        <form action="<?= site_url('page/box') ?>" method="post" class="row align-items-center mb-3">

            <div class="row align-items-center mb-3">
                <div class="col-sm-6 float-right pt-1" style="text-align: left;">
                    <h5>Pilih PlNO</h5>
                    <select class="form-control form-select btn-sm flat font-kecil" id="plSelect" name="selectedPlNo"
                        style="width: 60% !important; float: left; height: 31px; margin-right: 5px;"
                        onchange="this.form.submit()">

                        <option value="">-- Pilih PL No --</option>
                        <?php foreach ($pl_list as $row):
                            $sel = ($row['plno'] === $selectedPlNo); ?>
                            <option value="<?= html_escape($row['plno']) ?>" <?= $sel ? 'selected' : '' ?>>
                                <?= html_escape($row['plno']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-6" id="inputanrf">
                    <label for="inputrfid" class="col-sm-2 col-form-label pt-1 font-bold"
                        style="height: 26px;">RFID</label>
                    <div class="col-sm-10">
                        <input type="text" class="form-control input-sm flat p-0" id="inputrfid">
                    </div>
                </div>
            </div>
        </form>



        <!-- add space  -->
        <div class="row">
            <div class="col-lg-6">
                <div id="rekapvalid" class="py-3" style="font-weight: bold; color: black;"><?=  $progressPercent ?> Rekord telah tervalidasi dari ... Rekord ( ... Sisa)</div>
            </div>
        </div>
        <div class="row justify-content-center">
            <div class="col-12 d-flex flex-column" style="height: 500px;">
                <div id="tampungan" class="flex-grow-1 overflow-auto border rounded p-2">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th scope="col">No</th>
                            <th scope="col">P/O</th>
                            <th scope="col">No Bale</th>
                            <th scope="col">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($orders)): ?>
                            <tr>
                                <td class="text-center text-muted">Tidak ada data untuk PL: <?= html_escape($plno) ?></td>
                            </tr>
                        <?php else: ?>
                            <?php $no=1; foreach ($orders as $o): ?>
                                <tr>
                                    <th scope="row"><?= $no++ ?></th>
                                    <td><?= html_escape($o['po']) ?>
                                    / <?= html_escape($o['item']) ?>
                                    <?php if ($o['dis']): ?> dis <?= $o['dis'] ?><?php endif; ?></td>
                                    <td><?= $o['nobale'] ?></td>
                                    <td>
                                        <?php
                                        if (empty($o['masuk'])) {
                                            $st = 'NG';
                                        } else {
                                            $st = $o['status'] ?? 'OK';
                                        }

                                        // then map it to a Bootstrap badge color
                                        $cls = $st === 'OK'
                                            ? 'success'
                                            : ($st === 'SA'
                                                ? 'warning'
                                                : 'danger');
                                        ?>
                                        <span class="badge bg-<?= $cls ?>"><?= $st ?></span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>  
                    </tbody>
                </table>
            </div>
            </div>

            <div class="row justify-content-center">
                <div class="col-lg-6">
                    <div class="py-3"><!-- empty, adds vertical gap --></div>
                </div>
            </div>
            <div class=" w-50 mt-2 mx-auto text-center">
                <?= $doneOrdersCount ?>/<?= $totalOrdersCount ?>

                <div class="progress mb-2 mt-2">
                    <div class="progress-bar" role="progressbar" style="width: <?= $progressPercent ?>%;"
                        aria-valuenow="<?= $progressPercent ?>" aria-valuemin="0" aria-valuemax="100">
                        <?= $progressPercent ?>%
                    </div>
                </div>
                <?php if ($plno): ?>
                    <form method="post" action="<?= site_url('page/box') ?>" style="display:inline;">
                        <input type="hidden" name="selectedPlNo" value="<?= html_escape($plno) ?>">
                        <input type="hidden" name="plSelesai" value="1">
                        <button type="submit" class="btn btn-sm btn-warning" style="margin-left:8px;">
                            Selesaikan Orderan
                        </button>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>