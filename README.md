# StellarNews

This was my final project submission for IT202. It is a web application that serves users recent space articles from various sources utilizing the [SpaceNews API](https://rapidapi.com/JonasKenke/api/spacenews). 

This application eliminates the need to surf across numerous websites to see the current space news as it brings all the news in once place.

## Technologies Used:
* PHP (Backend)
* MySQL (Database)
* HTML/CSS with Bootstrap (Frontend)
* Git (Version Control)

## Project Breakdown/Features:
 The work was divided into three major milestones discussed below.
 
### Milestone 1: User Authentication & Authorization 
* Developed a system for user registration and login with client side and server side validation  
  * User credentials were stored in a database ensuring that passwords were hashed and usernames/emails were unique
* Editing profile functionality was added allowing users to change their credentials if they wanted
* Created an authorization system with roles  allowing for different roles to have different access to the site

### Milestone 2: Article Creation/Viewing
* Utilized MySQL database to store article data from the API after they were mapped/transformed
* Allowed those with admin role to create, edit, and disable articles directly on the website
* Displayed the article data in a user friendly format to allow users to access and read them
  * Filtering and sorting options also accompanied this to allow easy access to what the user wants

### Milestone 3: Favorites Association
* Implemented the user functionality to favorite articles allowing them to access specific articles easily at a later time
* Allowed admins to manage favorites and view user specific favorites, giving more insight into what users enjoy on the site

## Example Page:
![FavoritesPage](/media/ExampleFavoritesPage.png)