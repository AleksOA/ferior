const btnLogin = document.getElementById('login_input_1_5');
console.log(btnLogin)

const dataGform_1 = {
    'siteURL': '',
    'siteName': '',
};
function saveData(){
    console.log('saveData')
    let siteURL = document.getElementById('input_1_9').value;
    dataGform_1.siteURL= siteURL;
    let siteName = document.getElementById('input_1_10').value;
    dataGform_1.siteName= siteName;
}

if(btnLogin != null) {
    btnLogin.addEventListener('click', saveData);
}

console.log(dataGform_1);