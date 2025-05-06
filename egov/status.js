document.addEventListener('DOMContentLoaded', function() {
    const urlParams = new URLSearchParams(window.location.search);
    const reference = urlParams.get('ref');

    if(!reference) {
        document.getElementById('statusText').textContent = 'Error: No reference provided';
        return;
    }

    // status.js (corrected fetch URL)
    fetch(`http://localhost/egov/get_application.php?ref=${encodeURIComponent(reference)}`)
        .then(response => {
            if(!response.ok) throw new Error('Invalid reference');
            return response.json();
        })
        .then(data => {
            if(data.error) throw new Error(data.error);
            
            // Populate data
            document.getElementById('fullName').textContent = `${data.firstname} ${data.lastname}`;
            document.getElementById('fatherName').textContent = `${data.father_first_name} ${data.father_last_name}`;
            document.getElementById('motherName').textContent = `${data.mother_first_name} ${data.mother_last_name}`;
            document.getElementById('dobAd').textContent = data.dob_ad;
            document.getElementById('passportType').textContent = data.passport_type;
            document.getElementById('passportPages').textContent = data.pages;

            // Remove status-related code
            document.getElementById('statusText').textContent = 'Processing'; // Static status
        })
        .catch(error => {
            console.error('Error:', error);
            document.getElementById('statusText').textContent = 'Error: Invalid reference number';
            document.getElementById('statusText').classList.add('text-danger');
        });
});
