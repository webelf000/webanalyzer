<?php defined('ALTUMCODE') || die() ?>

    <div class="margin-bottom-6">
        <div class="row">
            <div class="col">
                <h5><?= $language->report->display->actions ?></h5>
            </div>

            <div class="col">
                <div class="btn-group" role="group">
                    <?php if($settings->store_unlock_report_price == '0' || $has_valid_report || (User::logged_in() && $account->type == '1')): ?>
                        <a href="api?api_key=<?= $account->api_key ?? 0 ?>&username=<?= $source_account->username ?>&source=<?= $source ?>" class="btn btn-light" target="_blank"><i class="fab fa-fw fa-keycdn text-muted"></i> <?= $language->report->display->api_link ?></a>
                        <button type="button" onclick="window.print()" class="btn btn-light"><i class="fa fa-fw fa-file-pdf text-muted"></i> <?= $language->report->display->pdf_link ?></button>
                    <?php endif ?>
                    <a href="compare/<?= $source ?>/<?= $source_account->username ?>" class="btn btn-light"><i class="fa fa-fw fa-users text-muted"></i> <?= $language->report->display->compare ?></a>
                </div>
            </div>
        </div>

        <?php if(count($logs) == 1): ?>
        <div class="alert alert-info mb-4 mt-2" role="alert">
            <?= $language->report->info_message->recently_generated ?>
        </div>
        <?php endif ?>
    </div>

    <div class="margin-bottom-6">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-center">
            <h2><?= $language->report->display->statistics_summary ?></h2>

            <?php if($settings->store_unlock_report_price == '0' || $has_valid_report || $source_account->is_demo): ?>
                <div>
                    <form class="form-inline" id="datepicker_form">
                        <input type="hidden" id="base_url" value="<?= $settings->url . 'report/' . $source_account->username . '/' . $source ?>" />

                        <div class="input-group input-group-datepicker">
                            <div class="input-group-prepend">
                                <div class="input-group-text"><i class="fa fa-fw fa-calendar-alt"></i></div>
                            </div>

                            <input
                                    type="text"
                                    class="form-control"
                                    id="datepicker_input"
                                    data-range="true"
                                    data-date-format="<?= str_replace(['Y', 'm', 'd', 'F'], ['yyyy', 'mm', 'dd', 'MM'], 'Y-m-d') ?>"
                                    data-min="<?= (new \DateTime($source_account->added_date))->format('Y-m-d') ?>"
                                    name="date_range"
                                    value="<?= ($date_string) ? $date_string : '' ?>"
                                    placeholder="<?= $language->global->date_range_selector ?>"
                                    autocomplete="off"
                            >
                        </div>

                        <button type="submit" class="btn btn-default"><?= $language->global->date_range_selector ?></button>
                    </form>
                </div>
            <?php endif ?>
        </div>

        <div class="chart-container">
            <canvas id="likes_chart"></canvas>
        </div>

        <?php if($source_account->followers): ?>
            <div class="chart-container mt-3">
                <canvas id="followers_chart"></canvas>
            </div>
        <?php endif ?>
    </div>

    <div class="margin-bottom-6">
        <div class="d-flex justify-content-between">
            <h2><?= $language->report->display->summary ?></h2>

            <a href="<?= csv_link_exporter(csv_exporter($logs, ['id', 'facebook_user_id'])) ?>" download="report.csv" target="_blank" class="align-self-start btn btn-light"><i class="fa fa-fw fa-file-csv"></i> <?= $language->global->export_csv ?></a>
        </div>
        <p class="text-muted"><?= $language->report->display->summary_help ?></p>

        <table class="table table-responsive-md">
            <thead class="thead-black bg-facebook">
            <tr>
                <th>
                    <?= $language->report->display->date ?>&nbsp;
                    <span data-toggle="tooltip" title="<?= sprintf($language->report->display->date_help, $language->global->date->datetime_format) ?>"><i class="fa fa-fw fa-question-circle"></i></span>
                </th>
                <th></th>
                <th><?= $language->facebook->report->display->likes ?></th>
                <th></th>
                <th><?= $language->facebook->report->display->followers ?></th>
                <th></th>
            </tr>
            </thead>
            <tbody>
            <?php
            $total_new = [
                'likes' => 0,
                'followers' => 0
            ];
            for($i = 0; $i < count($logs); $i++):
                $log_yesterday = ($i == count($logs) - 1) ? false : $logs[$i+1];
                $log = $logs[$i];
                $log_date = (new \DateTime($log['date']))->format($language->global->date->datetime_format);
                $log_date_name = $language->global->date->short_day_of_week->{(new \DateTime($log['date']))->format('N')};
                $likes_difference = $log_yesterday ? $log['likes'] - $log_yesterday['likes'] : 0;
                $followers_difference = $log_yesterday ? $log['followers'] - $log_yesterday['followers'] : 0;

                $total_new['likes'] += $likes_difference;
                $total_new['followers'] += $followers_difference;
                ?>
                <tr>
                    <td><?= $log_date ?></td>
                    <td><?= $log_date_name ?></td>
                    <td><?= nr($log['likes']) ?></td>
                    <td><?= colorful_number($likes_difference) ?></td>
                    <td><?= nr($log['followers']) ?></td>
                    <td><?= colorful_number($followers_difference) ?></td>
                </tr>
            <?php endfor ?>

                <tr class="bg-light">
                    <td colspan="2"><?= $language->report->display->total_summary ?></td>
                    <td colspan="2"><?= colorful_number($total_new['likes']) ?></td>
                    <td colspan="2"><?= colorful_number($total_new['followers']) ?></td>
                </tr>

            </tbody>
        </table>
    </div>

    <div class="margin-bottom-6">
        <h2><?= $language->report->display->projections ?></h2>
        <p class="text-muted"><?= $language->report->display->projections_help ?></p>

        <table class="table table-responsive-md">
            <thead class="thead-black">
            <tr>
                <th><?= $language->report->display->time_until ?></th>
                <th><?= $language->report->display->date ?></th>
                <th><?= $language->facebook->report->display->likes ?></th>
                <th><?= $language->facebook->report->display->followers ?></th>
            </tr>
            </thead>

            <tbody>
            <tr class="bg-light">
                <td><?= $language->report->display->time_until_now ?></td>
                <td><?= (new \DateTime())->format($language->global->date->datetime_format) ?></td>
                <td><?= nr($source_account->likes) ?></td>
                <td><?= nr($source_account->followers) ?></td>
            </tr>

            <?php if($total_days < 2): ?>

                <tr class="bg-light">
                    <td colspan="4"><?= $language->report->display->no_projections ?></td>
                </tr>

            <?php else: ?>
                <tr>
                    <td><?= sprintf($language->global->date->x_days, 30) ?></td>
                    <td><?= (new \DateTime())->modify('+30 day')->format($language->global->date->datetime_format) ?></td>
                    <td><?= nr($source_account->likes + ($average['likes'] * 30)) ?></td>
                    <td><?= nr($source_account->followers + ($average['followers'] * 30)) ?></td>
                </tr>

                <tr>
                    <td><?= sprintf($language->global->date->x_days, 60) ?></td>
                    <td><?= (new \DateTime())->modify('+60 day')->format($language->global->date->datetime_format) ?></td>
                    <td><?= nr($source_account->likes + ($average['likes'] * 60)) ?></td>
                    <td><?= nr($source_account->followers + ($average['followers'] * 60)) ?></td>
                </tr>

                <tr>
                    <td><?= sprintf($language->global->date->x_months, 3) ?></td>
                    <td><?= (new \DateTime())->modify('+90 day')->format($language->global->date->datetime_format) ?></td>
                    <td><?= nr($source_account->likes + ($average['likes'] * 90)) ?></td>
                    <td><?= nr($source_account->followers + ($average['followers'] * 90)) ?></td>
                </tr>

                <tr>
                    <td><?= sprintf($language->global->date->x_months, 6) ?></td>
                    <td><?= (new \DateTime())->modify('+180 day')->format($language->global->date->datetime_format) ?></td>
                    <td><?= nr($source_account->likes + ($average['likes'] * 180)) ?></td>
                    <td><?= nr($source_account->followers + ($average['followers'] * 180)) ?></td>
                </tr>

                <tr>
                    <td><?= sprintf($language->global->date->x_months, 9) ?></td>
                    <td><?= (new \DateTime())->modify('+270 day')->format($language->global->date->datetime_format) ?></td>
                    <td><?= nr($source_account->likes + ($average['likes'] * 270)) ?></td>
                    <td><?= nr($source_account->followers + ($average['followers'] * 270)) ?></td>
                </tr>

                <tr>
                    <td><?= sprintf($language->global->date->year) ?></td>
                    <td><?= (new \DateTime())->modify('+365 day')->format($language->global->date->datetime_format) ?></td>
                    <td><?= nr($source_account->likes + ($average['likes'] * 365)) ?></td>
                    <td><?= nr($source_account->followers + ($average['followers'] * 365)) ?></td>
                </tr>

                <tr>
                    <td><?= sprintf($language->global->date->year_and_half) ?></td>
                    <td><?= (new \DateTime())->modify('+547 day')->format($language->global->date->datetime_format) ?></td>
                    <td><?= nr($source_account->likes + ($average['likes'] * 547)) ?></td>
                    <td><?= nr($source_account->followers + ($average['followers'] * 547)) ?></td>
                </tr>

                <tr>
                    <td><?= sprintf($language->global->date->x_years, 2, $language->global->number->decimal_point, $language->global->number->thousands_separator) ?></td>
                    <td><?= (new \DateTime())->modify('+730 day')->format($language->global->date->datetime_format) ?></td>
                    <td><?= nr($source_account->likes + ($average['likes'] * 730)) ?></td>
                    <td><?= nr($source_account->followers + ($average['followers'] * 730)) ?></td>
                </tr>

                <tr class="bg-light">
                    <td colspan="2"><?= $language->report->display->average_calculations ?></td>
                    <td><?= sprintf($language->facebook->report->display->likes_per_day, colorful_number($average['likes'])) ?></td>
                    <td><?= sprintf($language->facebook->report->display->followers_per_day, colorful_number($average['followers'])) ?></td>
                </tr>

            <?php endif ?>
            </tbody>
        </table>
    </div>

</div>


<script>
    /* Datepicker */
    $('#datepicker_input').datepicker({
        language: 'en',
        autoClose: true,
        timepicker: false,
        toggleSelected: false,
        minDate: new Date($('#datepicker_input').data('min')),
        maxDate: new Date()
    });


    $('#datepicker_form').on('submit', (event) => {
        let date = $("#datepicker_input").val();

        let [ date_start, date_end ] = date.split(',');

        if(typeof date_end == 'undefined') {
            date_end = date_start
        }

        let base_url = $("#base_url").val();

        /* Redirect */
        window.location.href = `${base_url}/${date_start}/${date_end}`;

        event.preventDefault();
    });

    Chart.defaults.global.elements.line.borderWidth = 4;
    Chart.defaults.global.elements.point.radius = 3;
    Chart.defaults.global.elements.point.borderWidth = 7;


    let likes_chart_context = document.getElementById('likes_chart').getContext('2d');

    let gradient = likes_chart_context.createLinearGradient(0, 0, 0, 250);
    gradient.addColorStop(0, 'rgba(43, 227, 155, 0.6)');
    gradient.addColorStop(1, 'rgba(43, 227, 155, 0.05)');

    new Chart(likes_chart_context, {
        type: 'line',
        data: {
            labels: <?= $logs_chart['labels'] ?>,
            datasets: [{
                label: <?= json_encode($language->facebook->report->display->likes) ?>,
                data: <?= $logs_chart['likes'] ?>,
                backgroundColor: gradient,
                borderColor: '#2BE39B',
                fill: true
            }]
        },
        options: {
            tooltips: {
                mode: 'index',
                intersect: false,
                callbacks: {
                    label: (tooltipItem, data) => {
                        let value = data.datasets[tooltipItem.datasetIndex].data[tooltipItem.index];

                        return `${number_format(value, 0, '<?= $language->global->number->decimal_point ?>',  '<?= $language->global->number->thousands_separator ?>')} ${data.datasets[tooltipItem.datasetIndex].label}`;
                    }
                }
            },
            title: {
                text: <?= json_encode($language->facebook->report->display->likes_chart) ?>,
                display: true
            },
            legend: {
                display: false
            },
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                yAxes: [{
                    gridLines: {
                        display: false
                    },
                    ticks: {
                        userCallback: (value, index, values) => {
                            if(Math.floor(value) === value) {
                                return number_format(value, 0, '<?= $language->global->number->decimal_point ?>', '<?= $language->global->number->thousands_separator ?>');
                            }
                        }
                    }
                }],
                xAxes: [{
                    gridLines: {
                        display: false
                    }
                }]
            }
        }
    });


    let followers_chart_context = document.getElementById('followers_chart').getContext('2d');

    gradient = followers_chart_context.createLinearGradient(0, 0, 0, 250);
    gradient.addColorStop(0, 'rgba(62, 193, 255, 0.6)');
    gradient.addColorStop(1, 'rgba(62, 193, 255, 0.05)');

    new Chart(followers_chart_context, {
        type: 'line',
        data: {
            labels: <?= $logs_chart['labels'] ?>,
            datasets: [{
                label: <?= json_encode($language->facebook->report->display->followers) ?>,
                data: <?= $logs_chart['followers'] ?>,
                backgroundColor: gradient,
                borderColor: '#3ec1ff',
                fill: true
            }]
        },
        options: {
            tooltips: {
                mode: 'index',
                intersect: false,
                callbacks: {
                    label: (tooltipItem, data) => {
                        let value = data.datasets[tooltipItem.datasetIndex].data[tooltipItem.index];
                        return number_format(value, 0, '<?= $language->global->number->decimal_point ?>',  '<?= $language->global->number->thousands_separator ?>');
                    }
                }
            },
            title: {
                text: <?= json_encode($language->facebook->report->display->followers_chart) ?>,
                display: true
            },
            legend: {
                display: false
            },
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                yAxes: [{
                    gridLines: {
                        display: false
                    },
                    ticks: {
                        userCallback: (value, index, values) => {
                            if(Math.floor(value) === value) {
                                return number_format(value, 0, '<?= $language->global->number->decimal_point ?>', '<?= $language->global->number->thousands_separator ?>');
                            }
                        }
                    }
                }],
                xAxes: [{
                    gridLines: {
                        display: false
                    }
                }]
            }
        }
    });

</script>

