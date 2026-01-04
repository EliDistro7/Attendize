// public/js/ticket-pdf.js

import jsPDF from 'jspdf';
import html2canvas from 'html2canvas';

class TicketPDFGenerator {
    constructor() {
        this.pdf = null;
    }

    async generateFromHTML(element) {
        // Hide the download button before capturing
        const downloadBtn = document.querySelector('.download-section');
        if (downloadBtn) downloadBtn.style.display = 'none';

        try {
            // Capture the HTML as canvas
            const canvas = await html2canvas(element, {
                scale: 2,
                useCORS: true,
                logging: false,
                backgroundColor: '#1a2841'
            });

            // Show download button again
            if (downloadBtn) downloadBtn.style.display = 'block';

            // Calculate dimensions
            const imgWidth = 210; // A4 width in mm
            const pageHeight = 297; // A4 height in mm
            const imgHeight = (canvas.height * imgWidth) / canvas.width;
            let heightLeft = imgHeight;

            const imgData = canvas.toDataURL('image/png');
            
            // Create PDF
            this.pdf = new jsPDF('p', 'mm', 'a4');
            let position = 0;

            // Add first page
            this.pdf.addImage(imgData, 'PNG', 0, position, imgWidth, imgHeight);
            heightLeft -= pageHeight;

            // Add more pages if needed
            while (heightLeft >= 0) {
                position = heightLeft - imgHeight;
                this.pdf.addPage();
                this.pdf.addImage(imgData, 'PNG', 0, position, imgWidth, imgHeight);
                heightLeft -= pageHeight;
            }

            return this.pdf;
        } catch (error) {
            // Show download button again even on error
            if (downloadBtn) downloadBtn.style.display = 'block';
            throw error;
        }
    }

    async downloadTickets(orderReference) {
        const ticketsContainer = document.querySelector('.container');
        
        if (!ticketsContainer) {
            console.error('Tickets container not found');
            return;
        }

        try {
            // Show loading state
            const btn = document.querySelector('.download-btn');
            const originalText = btn.innerHTML;
            btn.innerHTML = '⏳ Generating PDF...';
            btn.disabled = true;

            // Generate PDF
            await this.generateFromHTML(ticketsContainer);
            
            // Download
            this.pdf.save(`tickets-${orderReference}.pdf`);
            
            // Restore button
            btn.innerHTML = originalText;
            btn.disabled = false;
        } catch (error) {
            console.error('PDF generation failed:', error);
            alert('Failed to generate PDF. Please try again.');
            
            // Restore button
            const btn = document.querySelector('.download-btn');
            btn.innerHTML = '📥 Download Tickets as PDF';
            btn.disabled = false;
        }
    }
}

// Export for use
window.TicketPDFGenerator = TicketPDFGenerator;