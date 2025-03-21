<?php
session_start();
require_once "config/db_config.php";

// Initialize variables
$action = isset($_GET['action']) ? $_GET['action'] : 'rate';
$error = null;
$result = null;
$currencies = ['EUR', 'USD', 'GBP', 'CHF', 'JPY', 'CZK', 'PLN', 'AUD', 'CAD', 'RON'];
$default_currency = 'EUR';

// Process form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        // Create SOAP client
        $client = new SoapClient('http://www.mnb.hu/arfolyamok.asmx?WSDL');
        
        if ($action == 'rate') {
            // Single day exchange rate
            $currency = $_POST['currency'];
            $date = $_POST['date'];
            
            // Format date for MNB API
            $formatted_date = date('Y-m-d', strtotime($date));
            
            // Get current exchange rates
            $result = $client->GetExchangeRates([
                'startDate' => $formatted_date,
                'endDate' => $formatted_date,
                'currencyNames' => $currency
            ]);
            
            // Parse XML result
            $xml = new SimpleXMLElement($result->GetExchangeRatesResult);
            $namespaces = $xml->getNamespaces(true);
            $mnb = $xml->children($namespaces['MNB']);
            
            // Extract exchange rate
            $rate = null;
            if (isset($mnb->Day)) {
                foreach ($mnb->Day->Rate as $rate_elem) {
                    if ((string)$rate_elem['curr'] == $currency) {
                        $rate = [
                            'currency' => $currency,
                            'date' => $date,
                            'rate' => (float)str_replace(',', '.', (string)$rate_elem)
                        ];
                        break;
                    }
                }
            }
            
            $result = $rate;
        } else if ($action == 'monthly') {
            // Monthly exchange rates
            $currency = $_POST['currency'];
            $year = $_POST['year'];
            $month = $_POST['month'];
            
            // Calculate start and end dates
            $start_date = date('Y-m-d', strtotime("$year-$month-01"));
            $end_date = date('Y-m-t', strtotime($start_date));
            
            // Get exchange rates for the month
            $result = $client->GetExchangeRates([
                'startDate' => $start_date,
                'endDate' => $end_date,
                'currencyNames' => $currency
            ]);
            
            // Parse XML result
            $xml = new SimpleXMLElement($result->GetExchangeRatesResult);
            $namespaces = $xml->getNamespaces(true);
            $mnb = $xml->children($namespaces['MNB']);
            
            // Extract exchange rates
            $rates = [];
            $dates = [];
            $values = [];
            
            if (isset($mnb->Day)) {
                foreach ($mnb->Day as $day) {
                    $day_date = (string)$day['date'];
                    
                    foreach ($day->Rate as $rate) {
                        if ((string)$rate['curr'] == $currency) {
                            $rate_value = (float)str_replace(',', '.', (string)$rate);
                            $rates[] = [
                                'date' => $day_date,
                                'rate' => $rate_value
                            ];
                            
                            // For chart data
                            $dates[] = $day_date;
                            $values[] = $rate_value;
                            break;
                        }
                    }
                }
            }
            
            $result = [
                'currency' => $currency,
                'year' => $year,
                'month' => $month,
                'rates' => $rates,
                'chart_data' => [
                    'dates' => $dates,
                    'values' => $values
                ]
            ];
        }
    } catch (Exception $e) {
        $error = "Error: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MNB Exchange Rates - ReNew Notebooks</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <?php include "includes/header.php"; ?>
    
    <div class="container py-5">
        <h1>Hungarian National Bank Exchange Rates</h1>
        
        <!-- Nav tabs -->
        <ul class="nav nav-tabs mb-4">
            <li class="nav-item">
                <a class="nav-link <?php echo $action == 'rate' ? 'active' : ''; ?>" href="mnb_service.php?action=rate">
                    Daily Exchange Rate
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo $action == 'monthly' ? 'active' : ''; ?>" href="mnb_service.php?action=monthly">
                    Monthly Exchange Rates
                </a>
            </li>
        </ul>
        
        <!-- Tab content -->
        <div class="tab-content">
            <!-- Daily Exchange Rate Form -->
            <?php if ($action == 'rate'): ?>
            <div class="tab-pane active">
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Get Exchange Rate for a Specific Date</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="mnb_service.php?action=rate">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="currency" class="form-label">Currency</label>
                                        <select class="form-select" id="currency" name="currency" required>
                                            <?php foreach ($currencies as $curr): ?>
                                                <option value="<?php echo $curr; ?>" <?php echo (isset($_POST['currency']) && $_POST['currency'] == $curr) || (!isset($_POST['currency']) && $curr == $default_currency) ? 'selected' : ''; ?>>
                                                    <?php echo $curr; ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="date" class="form-label">Date</label>
                                        <input type="date" class="form-control" id="date" name="date" required
                                               value="<?php echo isset($_POST['date']) ? $_POST['date'] : date('Y-m-d'); ?>">
                                    </div>
                                </div>
                            </div>
                            <button type="submit" class="btn btn-primary">Get Exchange Rate</button>
                        </form>
                    </div>
                </div>
                
                <?php if ($error): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php elseif ($result): ?>
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">Exchange Rate Result</h5>
                        </div>
                        <div class="card-body">
                            <div class="alert alert-info">
                                <h5>1 <?php echo htmlspecialchars($result['currency']); ?> = <?php echo $result['rate']; ?> HUF</h5>
                                <p>As of <?php echo date('F j, Y', strtotime($result['date'])); ?></p>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
            
            <!-- Monthly Exchange Rates Form -->
            <?php else: ?>
            <div class="tab-pane active">
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Get Monthly Exchange Rates</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="mnb_service.php?action=monthly">
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="currency" class="form-label">Currency</label>
                                        <select class="form-select" id="currency" name="currency" required>
                                            <?php foreach ($currencies as $curr): ?>
                                                <option value="<?php echo $curr; ?>" <?php echo (isset($_POST['currency']) && $_POST['currency'] == $curr) || (!isset($_POST['currency']) && $curr == $default_currency) ? 'selected' : ''; ?>>
                                                    <?php echo $curr; ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="year" class="form-label">Year</label>
                                        <select class="form-select" id="year" name="year" required>
                                            <?php for($y = date('Y'); $y >= 2000; $y--): ?>
                                                <option value="<?php echo $y; ?>" <?php echo (isset($_POST['year']) && $_POST['year'] == $y) || (!isset($_POST['year']) && $y == date('Y')) ? 'selected' : ''; ?>>
                                                    <?php echo $y; ?>
                                                </option>
                                            <?php endfor; ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="month" class="form-label">Month</label>
                                        <select class="form-select" id="month" name="month" required>
                                            <?php for($m = 1; $m <= 12; $m++): ?>
                                                <option value="<?php echo $m; ?>" <?php echo (isset($_POST['month']) && $_POST['month'] == $m) || (!isset($_POST['month']) && $m == date('n')) ? 'selected' : ''; ?>>
                                                <?php echo date('F', mktime(0, 0, 0, $m, 1)); ?>
                                                </option>
                                            <?php endfor; ?>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <button type="submit" class="btn btn-primary">Get Monthly Rates</button>
                        </form>
                    </div>
                </div>
                
                <?php if ($error): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php elseif ($result): ?>
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="mb-0">Exchange Rate Chart</h5>
                        </div>
                        <div class="card-body">
                            <canvas id="rateChart" width="400" height="200"></canvas>
                        </div>
                    </div>
                    
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">Exchange Rate Table</h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>Date</th>
                                            <th>1 <?php echo htmlspecialchars($result['currency']); ?> in HUF</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($result['rates'] as $rate): ?>
                                            <tr>
                                                <td><?php echo date('Y-m-d', strtotime($rate['date'])); ?></td>
                                                <td><?php echo $rate['rate']; ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    
                    <script>
                        // Initialize chart
                        document.addEventListener('DOMContentLoaded', function() {
                            const ctx = document.getElementById('rateChart').getContext('2d');
                            const chart = new Chart(ctx, {
                                type: 'line',
                                data: {
                                    labels: <?php echo json_encode($result['chart_data']['dates']); ?>,
                                    datasets: [{
                                        label: '<?php echo $result['currency']; ?> to HUF',
                                        data: <?php echo json_encode($result['chart_data']['values']); ?>,
                                        backgroundColor: 'rgba(54, 162, 235, 0.2)',
                                        borderColor: 'rgba(54, 162, 235, 1)',
                                        borderWidth: 1,
                                        tension: 0.1
                                    }]
                                },
                                options: {
                                    responsive: true,
                                    scales: {
                                        y: {
                                            beginAtZero: false
                                        }
                                    }
                                }
                            });
                        });
                    </script>
                <?php endif; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>
    
    <?php include "includes/footer.php"; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>