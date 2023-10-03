<html>
  <head>
    <meta charset="utf-8" />
    <script src="https://unpkg.com/pdf-lib@1.4.0"></script>
    <script src="https://unpkg.com/downloadjs@1.4.7"></script>
  </head>

  <body>
    <p>Click the button to copy pages from existing documents with <code>pdf-lib</code></p>
    <button onclick="copyPages()">Create PDF</button>
    <p class="small">(Your browser will download the resulting file)</p>
  </body>

  <script>
    const { PDFDocument } = PDFLib

    async function copyPages() {
			// Fetch first existing PDF document
      const url1 = 'https://pdf-lib.js.org/assets/with_update_sections.pdf'
      const firstDonorPdfBytes = await fetch(url1).then(res => res.arrayBuffer())

      // Load a PDFDocument from each of the existing PDFs
      const firstDonorPdfDoc = await PDFDocument.load(firstDonorPdfBytes)
      
      // Create a new PDFDocument
      const pdfDoc = await PDFDocument.create();

      // Copy the 1st page from the first donor document, and 
      // the 743rd page from the second donor document
      const [firstDonorPage] = await pdfDoc.copyPages(firstDonorPdfDoc, [0])
      const [secondDonorPage] = await pdfDoc.copyPages(secondDonorPdfDoc, [742])

      // Add the first copied page
      pdfDoc.addPage(firstDonorPage)

      // Insert the second copied page to index 0, so it will be the 
      // first page in `pdfDoc`
      pdfDoc.insertPage(0, secondDonorPage)

      // Serialize the PDFDocument to bytes (a Uint8Array)
      const pdfBytes = await pdfDoc.save()

			// Trigger the browser to download the PDF document
      download(pdfBytes, "pdf-lib_page_copying_example.pdf", "application/pdf");
    }
  </script>
</html>