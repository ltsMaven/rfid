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
            <div class="col-sm-6 float-right pt-1" style="text-align: right;">
                <select class="form-control form-select btn-sm flat font-kecil" id="alat" name="alat"
                    style="width: 60% !important; float: right; height: 31px; margin-right: 5px;">
                    <option value="fixedreader">Fixed Reader</option>
                    <option value="handheld">Handheld</option>
                </select>
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
        <div class="row justify-content-center">
            <div class="col-lg-6">
                <div class="py-3"><!-- empty, adds vertical gap --></div>
            </div>
        </div>
        <div class="row justify-content-center">
            <div class="col-lg-6 col-md-8 d-flex flex-column" style="height: 500px;">
                <div class="d-flex justify-content-end mb-2">
                    <form method="post" action="<?= site_url('page/box') ?>" style="display:inline;">
                        <!-- preserve the selected PL No -->
                        <input type="hidden" name="selectedPlNo" value="<?= html_escape($plno) ?>">
                        <?php if ($show_done): ?>
                            <!-- If we’re showing done, switch back to pending -->
                            <input type="hidden" name="show_done" value="0">
                            <button type="submit" class="btn btn-sm btn-primary">
                                Daftar order belum selesai
                            </button>
                        <?php else: ?>
                            <!-- Otherwise show the done list -->
                            <input type="hidden" name="show_done" value="1">
                            <button type="submit" class="btn btn-sm btn-primary">
                                Daftar order selesai
                            </button>
                        <?php endif; ?>
                    </form>
                </div>
                <div class="d-flex align-items-center mb-1">
                    <div class="flex-grow-1 font-bold">INPUT LIST</div>
                    <div class="text-end font-bold" id="posex" style="width:100px;">STATUS</div>
                </div>
                <hr class="my-1" />
                <div id="tampungan" class="flex-grow-1 overflow-auto border rounded p-2">
                    <?php if (empty($orders)): ?>
                        <div class="text-center text-muted">Tidak ada data untuk PL: <?= html_escape($plno) ?></div>
                    <?php else: ?>
                        <?php foreach ($orders as $o): ?>

                            <div class="row align-items-center mb-1">
                                <div class="col-7 text-start">
                                    <?= $o['id'] ?>. <?= html_escape($o['po']) ?>
                                    / <?= html_escape($o['item']) ?>
                                    <?php if ($o['dis']): ?> dis <?= $o['dis'] ?><?php endif; ?>
                                    Bale <?= $o['nobale'] ?>
                                </div>
                                <div class="col-2 text-center">
                                    <?= $o['masuk'] ?>
                                </div>
                                <div class="col-3 d-flex justify-content-end">
                                    <!-- if you have a status field in $o (e.g. OK/NG) -->
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
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

            <div class="row justify-content-center">
                <div class="col-lg-6">
                    <div class="py-3"><!-- empty, adds vertical gap --></div>
                </div>
            </div>
            <div class=" w-50 mt-2">
                <div class="progress">
                    <div class="progress-bar" role="progressbar" style="width: <?= $progressPercent ?>%;"
                        aria-valuenow="<?= $progressPercent ?>" aria-valuemin="0" aria-valuemax="100">
                        <?= $progressPercent ?>%</div>
                </div>
            </div>
        </div>
    </div>
</section>