# Appointment Management

Georgia Tech 2008 Computer Science capstone project.

## Overview

This was the Computer Science capstone project (CS 4911) at Georgia Tech during the summer of 2008. Students were split into teams of 4-6 and given a broad range of projects to choose from, and tasked with developing a project plan around the iterative design process and three project sprints: the first of 3 weeks, the second of 4, and the last of 3. Our project focused on developing a web-based appointment management and event synchronization application that could be used easily by users of varying demographics, that could be administrated simply, and which was robust enough to have its functionality extended in the future.

## Details

We worked in a team of four people.

* Amanda Glosson: Project Manager
* Shannon Quinn: Implementation Lead
* Chris Gray: Design and Documentation
* Robert Bush: Testing

 Our supervisor was Dr David Smith of the College of Computing at Georgia Tech. He tasked us with developing a PHP/MySQL based application which would allow users to register and set up appointments. At the time, he required three different types of users: administrators (which, at the time, consisted solely of him), counselors, and regular users. The application on a whole would keep a calendar of days and times for which users and counselors could sign up. The administrator would oversee all activity and approve appointments that were requested. The counselors would log on and use the calendar to indicate their times and days of availability. Regular users would log on, look at the calendar - which would have listed the available dates and times of open appointments - and request appointments.

Certain restrictions were built into the system in such a way that they could be lifted or expanded upon later. For instance, regular users were only allowed to register for dates and times for which at least two counselors - one of whom had to be of the same gender as the regular user - had indicated they were available. Furthermore, the administrator needed to be notified of these matchings, so he/she could approve or deny the requested appointment, and perhaps swap out counselors at the slot. Permissions for the users needed to be flexible as well, so they could be extended at a later time if and when the system grew.

Another twist to the project was the intense need for ease of use and the widest range of browser compatibility possible. This all but ruled out AJAX and Javascript, as well as Flash and any other client-side technologies that could be used to streamline the interface. Strict adherence to HTML and compatible CSS was required so the widest range of browser types and versions could be supported, and the user interface needed to be easy to use and intuitive.

We received excelling scores on our project. Each of us received outstanding final grades in the course (mine was an A). The project is still in production use today and has been further extended to meet the changing needs of the organization for which it was deployed.
