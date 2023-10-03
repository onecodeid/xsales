<?php
$b64Doc = chunk_split(base64_encode(file_get_contents($_GET['url'])));
$b64Doc = preg_replace("/[\n\r]/", "", $b64Doc);
$e = str_split($b64Doc, 76);
$f = [];
foreach ($e as $k => $v) $f[] = "'{$v}'\n";
$b64Doc = join('+', $f);
// echo $b64Doc;
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>

    <script src="//mozilla.github.io/pdf.js/build/pdf.js" crossorigin="anonymous"></script>
    <link href="//mozilla.github.io/pdf.js/web/viewer.css" rel="stylesheet" type="text/css" />
</head>

<body>
<canvas id="the-canvas"></canvas>
</body>
<script>
    // If absolute URL from the remote server is provided, configure the CORS
    // header on that server.
    var url = 'http://localhost/xsales/api/report/one_sales_009?sdate=2022-10-01&edate=2022-10-31';
    let x = <?= $b64Doc; ?>

    var pdfData = atob(x)

    // Loaded via <script> tag, create shortcut to access PDF.js exports.
    var pdfjsLib = window['pdfjs-dist/build/pdf'];

    // The workerSrc property shall be specified.
    pdfjsLib.GlobalWorkerOptions.workerSrc = '//mozilla.github.io/pdf.js/build/pdf.worker.js';

    // Asynchronous download of PDF
    var loadingTask = pdfjsLib.getDocument({data: pdfData});
    loadingTask.promise.then(function(pdf) {
        console.log('PDF loaded');

        // Fetch the first page
        var pageNumber = 1;
        pdf.getPage(pageNumber).then(function(page) {
            console.log('Page loaded');

            var scale = 1.5;
            var viewport = page.getViewport({
                scale: scale
            });

            // Prepare canvas using PDF page dimensions
            var canvas = document.getElementById('the-canvas');
            var context = canvas.getContext('2d');
            canvas.height = viewport.height;
            canvas.width = viewport.width;

            // Render PDF page into canvas context
            var renderContext = {
                canvasContext: context,
                viewport: viewport
            };
            var renderTask = page.render(renderContext);
            renderTask.promise.then(function() {
                console.log('Page rendered');
            });
        });
    }, function(reason) {
        // PDF loading error
        console.error(reason);
    });
</script>

</html>