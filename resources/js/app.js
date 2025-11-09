import './bootstrap';
import '../css/app.css';

// Ensure font sizes are applied after page load
document.addEventListener('DOMContentLoaded', () => {
    // Force font size application for any dynamically loaded content
    const style = document.createElement('style');
    style.textContent = `
        /* Additional font size enforcement */
        .text-xs, .text-sm, .text-base, .text-lg, .text-xl, .text-2xl, .text-3xl, .text-4xl {
            font-size: inherit !important;
        }
        
        /* Override Tailwind default font sizes */
        .text-xs { font-size: 14px !important; }
        .text-sm { font-size: 14px !important; }
        .text-base { font-size: 14px !important; }
        .text-lg { font-size: 20px !important; }
        .text-xl { font-size: 20px !important; }
        .text-2xl { font-size: 20px !important; }
        .text-3xl { font-size: 20px !important; }
        .text-4xl { font-size: 20px !important; }
    `;
    document.head.appendChild(style);
});
