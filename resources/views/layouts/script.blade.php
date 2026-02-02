    <!-- ///////////// Js Files ////////////////////  -->
    <!-- Core: jQuery (required by other libs) -->
    <script src="{{ asset('assets/js/lib/jquery-3.4.1.min.js') }}"></script>

    <!-- Bootstrap (defer so rendering isn't blocked). Popper can be deferred as well. -->
    <script src="{{ asset('assets/js/lib/popper.min.js') }}" defer></script>
    <script src="{{ asset('assets/js/lib/bootstrap.min.js') }}" defer></script>

    <!-- Ionicons (module/nomodule) - small and async-friendly -->
    <script type="module" src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.esm.js" defer></script>
    <script nomodule src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.js" defer></script>

    <!-- Plugins (deferred) -->
    <script src="{{ asset('assets/js/plugins/owl-carousel/owl.carousel.min.js') }}" defer></script>
    <script src="{{ asset('assets/js/plugins/jquery-circle-progress/circle-progress.min.js') }}" defer></script>

    <!-- WebcamJS and SweetAlert (defer) -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/webcamjs/1.0.26/webcam.min.js"></script>
    <script src="https://unpkg.com/sweetalert/dist/sweetalert.min.js" defer></script>
    <script src="https://unpkg.com/sweetalert/dist/sweetalert.min.js"></script>

    <!-- Base JS (app-specific) -->
    <script src="{{ asset('assets/js/base.js') }}" defer></script>

    <!-- Conditionally load AmCharts only if #chartdiv exists to save bandwidth on pages without charts -->
    <script>
        (function(){
            function loadScript(src, attrs){
                return new Promise(function(resolve, reject){
                    var s = document.createElement('script');
                    s.src = src;
                    if(attrs){
                        Object.keys(attrs).forEach(function(k){ s.setAttribute(k, attrs[k]); });
                    }
                    s.onload = resolve;
                    s.onerror = reject;
                    document.head.appendChild(s);
                });
            }

            if(document.getElementById('chartdiv')){
                // Load amcharts libs in sequence then initialize
                loadScript('https://cdn.amcharts.com/lib/4/core.js')
                .then(function(){ return loadScript('https://cdn.amcharts.com/lib/4/charts.js'); })
                .then(function(){ return loadScript('https://cdn.amcharts.com/lib/4/themes/animated.js'); })
                .then(function(){
                    // initialize chart if needed (guarded to avoid errors)
                    if(window.am4core && window.am4charts){
                        am4core.ready(function () {
                            am4core.useTheme(am4themes_animated);
                            var chart = am4core.create("chartdiv", am4charts.PieChart3D);
                            chart.hiddenState.properties.opacity = 0;
                            chart.legend = new am4charts.Legend();
                            chart.data = [
                                { country: "Hadir", litres: 501.9 },
                                { country: "Sakit", litres: 301.9 },
                                { country: "Izin", litres: 201.1 },
                                { country: "Terlambat", litres: 165.8 }
                            ];
                            var series = chart.series.push(new am4charts.PieSeries3D());
                            series.dataFields.value = "litres";
                            series.dataFields.category = "country";
                            series.alignLabels = false;
                            series.labels.template.text = "{value.percent.formatNumber('#.0')}%";
                            series.labels.template.radius = am4core.percent(-40);
                            series.labels.template.fill = am4core.color("white");
                            series.colors.list = [
                                am4core.color("#1171ba"),
                                am4core.color("#fca903"),
                                am4core.color("#37db63"),
                                am4core.color("#ba113b")
                            ];
                        });
                    }
                }).catch(function(err){
                    // Silently fail - chart won't render but page stays usable
                    console.warn('Failed to load amcharts:', err);
                });
            }
        })();
    </script>

    @stack('myscripts')
