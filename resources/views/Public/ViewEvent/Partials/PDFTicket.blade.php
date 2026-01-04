<html>
<head>
    <title>Ticket(s)</title>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1.0">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            font-family: 'Arial', sans-serif;
            padding: 20px;
            min-height: 100vh;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
        }

        .ticket {
            background: linear-gradient(135deg, rgba(26, 40, 65, 0.85) 0%, rgba(45, 69, 99, 0.9) 50%, rgba(26, 40, 65, 0.85) 100%);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.18);
            border-radius: 20px;
            padding: 40px;
            margin-bottom: 40px;
            position: relative;
            overflow: hidden;
            box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.37);
            page-break-after: always;
        }

        /* PDF-friendly version - solid background */
        @media print {
            body {
                background: #1a2841 !important;
                padding: 0;
            }

            .ticket {
                background: linear-gradient(135deg, #1a2841 0%, #2d4563 50%, #1a2841 100%) !important;
                backdrop-filter: none !important;
                -webkit-backdrop-filter: none !important;
                border: 2px solid #4a5f7f;
                box-shadow: none;
                page-break-after: always;
                margin-bottom: 0;
            }

            .ticket::before {
                opacity: 0.3;
            }
        }

        /* Rainbow effect overlay */
        .ticket::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -20%;
            width: 100%;
            height: 200%;
            background: radial-gradient(ellipse at center, 
                rgba(255, 107, 107, 0.15) 0%,
                rgba(255, 193, 7, 0.1) 25%,
                rgba(76, 175, 80, 0.1) 50%,
                rgba(33, 150, 243, 0.15) 75%,
                rgba(156, 39, 176, 0.1) 100%);
            pointer-events: none;
        }

        .ticket-header {
            text-align: center;
            margin-bottom: 40px;
            position: relative;
            z-index: 1;
        }

        .event-title {
            font-size: 48px;
            font-weight: bold;
            color: #ffffff;
            text-transform: uppercase;
            letter-spacing: 4px;
            margin-bottom: 10px;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.5);
        }

        .ticket-type-header {
            font-size: 32px;
            font-weight: bold;
            color: #d4af37;
            text-transform: uppercase;
            letter-spacing: 2px;
            text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.5);
        }

        .ticket-content {
            display: flex;
            gap: 40px;
            position: relative;
            z-index: 1;
            align-items: flex-start;
        }

        .qr-section {
            flex-shrink: 0;
        }

        .qr-container {
            background: #ffffff;
            padding: 20px;
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.3);
            display: inline-block;
        }

        .qr-container svg {
            display: block;
        }

        .details-section {
            flex-grow: 1;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px 40px;
        }

        .detail-item h4 {
            font-size: 14px;
            color: #d4af37;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 8px;
            font-weight: 600;
        }

        .detail-item p {
            font-size: 20px;
            color: #ffffff;
            font-weight: 500;
            line-height: 1.4;
        }

        .event-poster {
            flex-shrink: 0;
            width: 200px;
            position: relative;
            z-index: 1;
        }

        .event-poster img {
            width: 100%;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.4);
        }

        .ticket-footer {
            margin-top: 40px;
            padding-top: 30px;
            border-top: 2px solid rgba(255, 255, 255, 0.2);
            display: flex;
            justify-content: space-around;
            text-align: center;
            position: relative;
            z-index: 1;
        }

        .footer-item {
            color: #ffffff;
            font-size: 18px;
            font-weight: 500;
        }

        .barcode-1d {
            margin-top: 20px;
            text-align: center;
            background: white;
            padding: 10px;
            border-radius: 10px;
            display: inline-block;
        }

        .bottom_info {
            text-align: center;
            padding: 20px;
            margin-top: 20px;
            color: #666;
        }

        .bottom_info a {
            color: #333;
            text-decoration: none;
        }

        .download-section {
            text-align: center;
            margin: 30px 0;
            padding: 20px;
        }

        .download-btn {
            display: inline-block;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 15px 40px;
            border-radius: 10px;
            text-decoration: none;
            font-size: 18px;
            font-weight: bold;
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .download-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(102, 126, 234, 0.6);
            color: white;
        }

        .download-btn i {
            margin-right: 10px;
        }

        @media print {
            .download-section {
                display: none;
            }
        }

        /* Fixed width - no responsive collapse */
        .ticket {
            min-width: 1100px;
        }

        @media print {
            body {
                background: #1a2841 !important;
                padding: 0;
            }

            .ticket {
                background: linear-gradient(135deg, #1a2841 0%, #2d4563 50%, #1a2841 100%) !important;
                backdrop-filter: none !important;
                -webkit-backdrop-filter: none !important;
                border: 2px solid #4a5f7f;
                box-shadow: none;
                page-break-after: always;
                margin-bottom: 0;
            }

            .ticket::before {
                opacity: 0.3;
            }

            .bottom_info {
                page-break-before: avoid;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Download Button Section -->
      <div class="download-section">
            <button onclick="handleDownload()" class="download-btn">
    📥 Download Tickets as PDF
</button>
        </div>

        @foreach($attendees as $attendee)
            @if(!$attendee->is_cancelled)
                <div class="ticket">
                    <!-- Header Section -->
                    <div class="ticket-header">
                        <div class="event-title">{{$event->title}}</div>
                        <div class="ticket-type-header">{{$attendee->ticket->title}}</div>
                    </div>

                    <!-- Main Content -->
                    <div class="ticket-content">
                        <!-- QR Code Section -->
                        <div class="qr-section">
                            <div class="qr-container">
                                {!! DNS2D::getBarcodeSVG($attendee->private_reference_number, "QRCODE", 8, 8) !!}
                            </div>
                            @if($event->is_1d_barcode_enabled)
                                <div class="barcode-1d">
                                    {!! DNS1D::getBarcodeSVG($attendee->private_reference_number, "C39+", 1, 50) !!}
                                </div>
                            @endif
                        </div>

                        <!-- Details Section -->
                        <div class="details-section">
                            <div class="detail-item">
                                <h4>@lang("Ticket.name")</h4>
                                <p>{{$attendee->first_name.' '.$attendee->last_name}}</p>
                            </div>

                            <div class="detail-item">
                                <h4>@lang("Ticket.venue")</h4>
                                <p>{{$event->venue_name}}</p>
                            </div>

                            <div class="detail-item">
                                <h4>@lang("Ticket.start_date_time")</h4>
                                <p>{{$event->startDateFormatted()}}</p>
                            </div>

                            <div class="detail-item">
                                <h4>@lang("Ticket.attendee_ref")</h4>
                                <p>{{$attendee->reference}}</p>
                            </div>

                            <div class="detail-item">
                                <h4>@lang("Ticket.end_date_time")</h4>
                                <p>{{$event->endDateFormatted()}}</p>
                            </div>

                            <div class="detail-item">
                                <h4>@lang("Ticket.price")</h4>
                                <p>
                                    @php
                                        $grand_total = $attendee->ticket->total_price;
                                        $tax_amt = ($grand_total * $event->organiser->tax_value) / 100;
                                        $grand_total = $tax_amt + $grand_total;
                                    @endphp
                                    {{money($grand_total, $order->event->currency)}}
                                </p>
                            </div>
                        </div>

                        <!-- Event Poster -->
                        <div class="event-poster">
                            @if($image)
                                <img src="data:image/png;base64,{{$image}}" alt="Event Poster" />
                            @endif
                        </div>
                    </div>

                    <!-- Footer Section -->
                    <div class="ticket-footer">
                        <div class="footer-item">{{date('Y')}}</div>
                        <div class="footer-item">{{$event->organiser->name}}</div>
                        <div class="footer-item">@lang("Ticket.order_ref"): {{$order->order_reference}}</div>
                        <div class="footer-item">{{parse_url(config('app.url'), PHP_URL_HOST)}}</div>
                    </div>
                </div>
            @endif
        @endforeach

        <div class="bottom_info">
            @include('Shared.Partials.PoweredBy')
        </div>

        
    </div>
<script src="https://unpkg.com/jspdf@latest/dist/jspdf.umd.min.js"></script>
<script src="https://unpkg.com/html2canvas@latest/dist/html2canvas.min.js"></script>

<script>
    async function downloadTicketsPDF(orderReference) {
        if (!window.jspdf || !window.html2canvas) {
            alert('PDF libraries not loaded. Please refresh.');
            return;
        }
        
        const { jsPDF } = window.jspdf;
        const btn = document.querySelector('.download-btn');
        const downloadSection = document.querySelector('.download-section');
        const bottomInfo = document.querySelector('.bottom_info');
        const originalText = btn.innerHTML;
        
        try {
            btn.innerHTML = '⏳ Generating PDF...';
            btn.disabled = true;
            
            // Hide elements we don't want in PDF
            if (bottomInfo) bottomInfo.style.display = 'none';
            downloadSection.style.display = 'none';
            
            // Replace all QR SVGs with canvas
            const qrContainers = document.querySelectorAll('.qr-container');
            
            for (let container of qrContainers) {
                const svg = container.querySelector('svg');
                if (svg) {
                    const canvas = document.createElement('canvas');
                    const size = 200;
                    canvas.width = size;
                    canvas.height = size;
                    const ctx = canvas.getContext('2d');
                    
                    const svgData = new XMLSerializer().serializeToString(svg);
                    const img = new Image();
                    const svgBlob = new Blob([svgData], { type: 'image/svg+xml;charset=utf-8' });
                    const url = URL.createObjectURL(svgBlob);
                    
                    await new Promise((resolve) => {
                        img.onload = () => {
                            ctx.fillStyle = 'white';
                            ctx.fillRect(0, 0, size, size);
                            ctx.drawImage(img, 0, 0, size, size);
                            URL.revokeObjectURL(url);
                            
                            svg.parentNode.replaceChild(canvas, svg);
                            resolve();
                        };
                        img.src = url;
                    });
                }
            }
            
            // Handle 1D barcodes
            const barcodes = document.querySelectorAll('.barcode-1d svg');
            for (let svg of barcodes) {
                const canvas = document.createElement('canvas');
                const width = parseInt(svg.getAttribute('width')) || 300;
                const height = parseInt(svg.getAttribute('height')) || 50;
                canvas.width = width;
                canvas.height = height;
                const ctx = canvas.getContext('2d');
                
                const svgData = new XMLSerializer().serializeToString(svg);
                const img = new Image();
                const svgBlob = new Blob([svgData], { type: 'image/svg+xml;charset=utf-8' });
                const url = URL.createObjectURL(svgBlob);
                
                await new Promise((resolve) => {
                    img.onload = () => {
                        ctx.fillStyle = 'white';
                        ctx.fillRect(0, 0, width, height);
                        ctx.drawImage(img, 0, 0, width, height);
                        URL.revokeObjectURL(url);
                        
                        svg.parentNode.replaceChild(canvas, svg);
                        resolve();
                    };
                    img.src = url;
                });
            }
            
            // Wait for images to settle
            await new Promise(resolve => setTimeout(resolve, 500));
            
            // Get all tickets
            const tickets = document.querySelectorAll('.ticket');
            const pdf = new jsPDF('p', 'mm', 'a4');
            
            const pageWidth = 210;  // A4 width in mm
            const pageHeight = 297; // A4 height in mm
            const margin = 10;      // 10mm margin on all sides
            const availableWidth = pageWidth - (margin * 2);
            const availableHeight = pageHeight - (margin * 2);
            
            for (let i = 0; i < tickets.length; i++) {
                if (i > 0) pdf.addPage();
                
                // Capture the ticket with high quality
                const ticketCanvas = await html2canvas(tickets[i], {
                    scale: 3, // Higher quality
                    useCORS: true,
                    allowTaint: true,
                    backgroundColor: '#1a2841',
                    logging: false,
                    width: 1200,  // Fixed width for consistency
                    windowWidth: 1200
                });
                
                const imgData = ticketCanvas.toDataURL('image/png', 1.0);
                
                // Calculate dimensions to fit within available space
                const aspectRatio = ticketCanvas.width / ticketCanvas.height;
                let imgWidth = availableWidth;
                let imgHeight = imgWidth / aspectRatio;
                
                // If height exceeds available space, scale by height instead
                if (imgHeight > availableHeight) {
                    imgHeight = availableHeight;
                    imgWidth = imgHeight * aspectRatio;
                }
                
                // Center the image on the page
                const xOffset = (pageWidth - imgWidth) / 2;
                const yOffset = (pageHeight - imgHeight) / 2;
                
                // Add background color to entire page
                pdf.setFillColor(26, 40, 65); // #1a2841
                pdf.rect(0, 0, pageWidth, pageHeight, 'F');
                
                // Add the ticket image centered
                pdf.addImage(imgData, 'PNG', xOffset, yOffset, imgWidth, imgHeight, '', 'FAST');
            }
            
            // Restore UI elements
            if (bottomInfo) bottomInfo.style.display = 'block';
            downloadSection.style.display = 'block';
            
            // Download the PDF
            pdf.save(`tickets-${orderReference}.pdf`);
            
        } catch (error) {
            console.error('PDF generation failed:', error);
            alert('Failed to generate PDF: ' + error.message);
            
            // Restore UI even on error
            if (bottomInfo) bottomInfo.style.display = 'block';
        } finally {
            downloadSection.style.display = 'block';
            btn.innerHTML = originalText;
            btn.disabled = false;
        }
    }
    
    function handleDownload() {
        downloadTicketsPDF('{{ $order->order_reference }}');
    }
</script>
</body>
</html>