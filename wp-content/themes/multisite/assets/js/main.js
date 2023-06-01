// add data from Gform_1 to  Gform_3
const btnLogin = document.getElementById('login_input_1_5');
const siteURL_input_3_1 = document.getElementById('input_3_1');
const siteName_input_3_3 = document.getElementById('input_3_3');

function saveData(){
    let siteURL = document.getElementById('input_1_9').value;
    let siteName = document.getElementById('input_1_10').value;

    if(siteURL != null && siteURL != "" ) {
        localStorage.setItem('siteURL', siteURL );
    }
    if(siteName != null && siteName != "" ) {
        localStorage.setItem('siteName', siteName );
    }
}

if(btnLogin != null) {
    btnLogin.addEventListener('click', saveData);
}
if(siteURL_input_3_1 != null){
    let siteURL = localStorage.getItem('siteURL');
    if(siteURL != null && siteURL != undefined) {
        siteURL_input_3_1.value = siteURL;
        localStorage.removeItem('siteURL');
    }
}

if(siteName_input_3_3 != null){
    let siteName = localStorage.getItem('siteName');
    if(siteName != null && siteName != undefined) {
        siteName_input_3_3.value = siteName;
        localStorage.removeItem('siteName');
    }
}
// =================================================