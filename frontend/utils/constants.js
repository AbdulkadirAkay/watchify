let Constants = {
  PROJECT_BASE_URL: "http://localhost/watchify/backend/",
  USER_ROLE: "user",
  ADMIN_ROLE: "admin",
};

if(window.location.hostname === 'localhost' || window.location.hostname === '127.0.0.1'){
  Constants.PROJECT_BASE_URL = "http://localhost/watchify/backend/";
} else {
  Constants.PROJECT_BASE_URL = "https://watchify-7ut3s.ondigitalocean.app/";
}