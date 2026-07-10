        </div>
        <!-- Bootstrap core JS-->
        <!-- <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script> -->
        <script src="<?= base_url(); ?>assets/js/bootstrap.bundle.min.js"></script>
        <script src="<?= base_url(); ?>assets/js/jquery-3.7.1.js"></script>
        <!-- Core theme JS-->
        <?php $unik = '1750228048'; ?>
        <script src="<?= base_url(); ?>assets/js/scripts.js"></script>
        <?php if(isset($halaman) && $halaman=='fn'): ?>
            <script src="<?= base_url(); ?>assets/js/fixed-readerant.js?<?= $unik ?>"></script>
        <?php endif; ?>
        <?php if(isset($halaman) && $halaman=='gf'): ?>
            <script src="<?= base_url(); ?>assets/js/fixed-readerantgf.js?<?= $unik ?>"></script>
        <?php endif; ?>
        <?php if(isset($halaman) && $halaman=='bx'): ?>
<script src="<?= base_url(); ?>assets/js/fixed-readerantbox.js?<?= $unik ?>"></script>
        <?php endif; ?>
    </body>
</html>