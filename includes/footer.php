<footer class="bg-dark text-white py-4 mt-5">
    <div class="container">
        <div class="row">
            <div class="col-md-6">
                <h5>ReNew Notebooks</h5>
                <p>ReNew Ltd. sells refurbished notebooks at favorable prices in the capital of Nowhereland.</p>
            </div>
            <div class="col-md-3">
                <h5>Quick Links</h5>
                <ul class="list-unstyled">
                    <li><a href="<?php echo (strpos($_SERVER['PHP_SELF'], '/admin/') !== false) ? '../index.php' : 'index.php'; ?>" class="text-white">Home</a></li>
                    <li><a href="<?php echo (strpos($_SERVER['PHP_SELF'], '/admin/') !== false) ? '../notebooks.php' : 'notebooks.php'; ?>" class="text-white">Notebooks</a></li>
                    <li><a href="<?php echo (strpos($_SERVER['PHP_SELF'], '/admin/') !== false) ? '../mnb_service.php' : 'mnb_service.php'; ?>" class="text-white">Exchange Rates</a></li>
                </ul>
            </div>
            <div class="col-md-3">
                <h5>Contact</h5>
                <address>
                    <p>123 Computer Street<br>
                    Capital City<br>
                    Nowhereland</p>
                    <p>Email: info@renew-notebooks.com<br>
                    Phone: +1 234 567 890</p>
                </address>
            </div>
        </div>
        <hr>
        <div class="text-center">
            <p>&copy; <?php echo date("Y"); ?> ReNew Ltd. All rights reserved.</p>
        </div>
    </div>
</footer>