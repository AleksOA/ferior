
const btnTest = document.getElementById('btnTest');

function openLoginPage() {
    let siteAddressURL = document.getElementById('mainFormSiteURL').value;
    window.location.href = `http://${siteAddressURL}.ferior.com.ua/wp-login.php`;
}

if(document.querySelector('.page-template-home_template') != null){
    // document.addEventListener( 'wpcf7submit', openLoginPage);
    document.addEventListener( 'wpcf7mailsent', openLoginPage);
}


// function updateURL(){
//     console.log('updateURL')
//     // let baseUrl = window.location.protocol + "//" + window.location.host + window.location.pathname;
//     // let newUrl = `${baseUrl}`;
//     // let newUrl = `site2.multisite.loc/wp-login.php`;
//     // history.pushState(null, null, newUrl);
//     // console.log(newUrl)
//
//     window.location.href = `http://site2.multisite.loc/wp-login.php`;
// }
//
// document.addEventListener( 'click', updateURL);