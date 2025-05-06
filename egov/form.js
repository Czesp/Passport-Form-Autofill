const provinceDistricts = {
    '1': ['Bhojpur', 'Dhankuta', 'Ilam', 'Jhapa', 'Khotang', 'Morang', 'Okhaldhunga', 
          'Panchthar', 'Sankhuwasabha', 'Solukhumbu', 'Sunsari', 'Taplejung', 'Terhathum', 'Udayapur'],
    '2': ['Parsa', 'Bara', 'Rautahat', 'Sarlahi', 'Dhanusha', 'Mahottari', 'Siraha', 'Saptari'],
    '3': ['Kathmandu', 'Lalitpur', 'Bhaktapur', 'Dhading', 'Nuwakot', 'Rasuwa', 'Sindhupalchok',
          'Kavrepalanchok', 'Makwanpur', 'Chitwan'],
    '4': ['Baglung', 'Gorkha', 'Kaski', 'Lamjung', 'Manang', 'Mustang', 'Myagdi',
          'Nawalpur', 'Parbat', 'Syangja', 'Tanahun'],
    '5': ['Arghakhanchi', 'Banke', 'Bardiya', 'Dang', 'Gulmi', 'Kapilvastu', 'Palpa', 
          'Nawalparasi (West)', 'Pyuthan', 'Rolpa', 'Rupandehi'],
    '6': ['Dolpa', 'Humla', 'Jumla', 'Kalikot', 'Mugu', 'Salyan', 'Surkhet',
          'Dailekh', 'Jajarkot'],
    '7': ['Achham', 'Baitadi', 'Bajhang', 'Bajura', 'Dadeldhura', 'Darchula', 'Kailali',
          'Kanchanpur', 'Doti']
};


document.getElementById('province').addEventListener('change', function() {
    const districtSelect = document.getElementById('district');
    districtSelect.innerHTML = '<option value="">Select District</option>';
    
    if(this.value in provinceDistricts) {
        provinceDistricts[this.value].forEach(district => {
            districtSelect.innerHTML += `<option value="${district}">${district}</option>`;
        });
    }
});

async function fetchNIDDetails() {
    const nid = document.getElementById('nid').value;
    if (!nid) {
        alert('Please enter NID number');
        return;
    }

    try {
        // Absolute URL for both environments
        const url = `http://localhost/egov/fetch_nid.php?nid=${encodeURIComponent(nid)}`;
        
        const response = await fetch(url);
        
        if (!response.ok) {
            const error = await response.json();
            throw new Error(error.error || 'Failed to fetch data');
        }
        
        const data = await response.json();
        
        if (data.error) {
            alert(data.error);
        } else {
            document.getElementById('lastname').value = data.lastname || '';
            document.getElementById('firstname').value = data.firstname || '';
            document.getElementById('dob_ad').value = data.dob_ad || '';
            document.getElementById('dob_bs').value = data.dob_bs || '';
            document.getElementById('birth_place').value = data.birth_place || '';
            document.getElementById('father_last_name').value = data.father_last_name || '';
            document.getElementById('father_first_name').value = data.father_first_name || '';
            document.getElementById('mother_last_name').value = data.mother_last_name || '';
            document.getElementById('mother_first_name').value = data.mother_first_name || '';
            document.getElementById('citizenship_number').value = data.citizenship_number || '';
            
            if(data.gender) {
                document.querySelector(`input[name="gender"][value="${data.gender}"]`).checked = true;
            }
        }
    } catch (error) {
        console.error('Fetch error:', error);
        alert(`Error: ${error.message}`);
    }
}

// Form submission handler
document.getElementById('passportForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    
    try {
        const url = 'http://localhost/egov/submit.php';
        const response = await fetch(url, {
            method: 'POST',
            body: new FormData(e.target)
        });
        
        if (!response.ok) {
            const error = await response.json();
            throw new Error(error.error || 'Submission failed');
        }
        
        const result = await response.json();
        window.location.href = `success.html?ref=${result.reference}`;
        
    } catch (error) {
        console.error('Submission error:', error);
        alert(`Error: ${error.message}`);
    }
});