window.day1 = new Date();
window.day2 = new Date();
window.day3 = new Date();
window.day4 = new Date();
window.day2.setDate(day1.getDate() + 1);
window.day3.setDate(day1.getDate() + 2);
window.day4.setDate(day1.getDate() + 3);

window.messages = {
    1 : {
        'id' : 1,
        'type' : 'event',
        'title' : 'Math Placement Test',
        'archived' : 1,
        'author' : 'Bayside College',
        'receivedDate' : new Date(2012,3,1),
        'teaser' : 'Your math placement test has been scheduled for 3/18/2012...',
        'body' : 'Your math placement test has been scheduled for 3/18/2012.  The test will be given in the west wing of Tuttle Center for Mathematics, Science, and Computer Education.  Good luck!',
        'deleted' : 0,
        'read' : 1,
        'priority' : 0,
        'subTypes' : {
            'event' : {
                'date' : new Date(2012,3,18)
            }
        }
        
    },
    2 : {
        'id' : 2,
        'type' : 'event',
        'title' : 'Move in day',
        'archived' : 0,
        'author' : 'Bayside College',
        'receivedDate' : new Date(2012,3,15),
        'teaser' : 'Welcome to Bayside College!  You should have...',
        'body' : 'Welcome to Bayside College!  You should have received a separate notification back in July about your housing assignment.  Your move-in day is scheduled for Friday, August 24th, beginning at 8am.  Please proceed to the front desk at Belding Hall to check-in and get your room key for the year.  See you then!',
        'deleted' : 0,
        'read' : 0,
        'priority' : 0,
        'subTypes' : {
            'event' : {
                'date' : day2
            }
        }
        
    },
    3 : {
        'id' : 3,
        'type' : 'event',
        'title' : 'New student orientation',
        'archived' : 0,
        'author' : 'Bayside College',
        'receivedDate' : new Date(2012,3,16),
        'teaser' : 'Here at Bayside College, we like ...',
        'body' : 'Here at Bayside College, we like to make sure all of our new students are oriented properly.  Join the rest of your Freshman class in Bliss Auditorium for a session led by Student Body President Zack Morris III.  Doors open at 7pm.',
        'deleted' : 0,
        'read' : 0,
        'priority' : 0,
        'subTypes' : {
            'event' : {
                'date' : day3
            }
        }
        
    },
    4 : {
        'id' : 4,
        'type' : 'event',
        'title' : 'First day of class',
        'archived' : 0,
        'author' : 'Bayside College',
        'receivedDate' : new Date(2012,3,16),
        'teaser' : 'Classes begin at Bayside College on Monday...',
        'body' : 'Classes begin at Bayside College on Monday, August 27th.  Your schedule for Monday is as follows:<br /><br />9am- ENGL 101: Literacy and You<br />11am- PHIL 101: Introduction to Philosophy<br />2pm- ENGL 102: American Literature',
        'deleted' : 0,
        'read' : 0,
        'priority' : 1,
        'subTypes' : {
            'event' : {
                'date' : day4
            }
        }
        
    },
    
    5 : {
        'id' : 5,
        'type' : 'todo',
        'title' : 'Register for classes',
        'archived' : 0,
        'author' : 'me',
        'receivedDate' : new Date(2012,3,16),
        'teaser' : '',
        'body' : '',
        'deleted' : 0,
        'read' : 0,
        'priority' : 0,
        'subTypes' : {
            'todo' : {
                'done' : 1,
                'notes' : 'Got the classes I wanted.'
            },
            'due' : {
                'date' : day1
            }
        }
    },
    6 : {
        'id' : 6,
        'type' : 'todo',
        'title' : 'Email roommate',
        'archived' : 0,
        'author' : 'me',
        'receivedDate' : new Date(2012,3,19),
        'teaser' : 'email address: esthermcdonald29049@gmail.com',
        'body' : 'email address: esthermcdonald29049@gmail.com',
        'deleted' : 0,
        'read' : 0,
        'priority' : 0,
        'subTypes' : {
            'todo' : {
                'done' : 0,
                'notes' : ''
            },
            'due' : {
                'date' : day1
            }
        }    
    },
    7 : {
        'id' : 7,
        'type' : 'todo',
        'title' : 'Buy school supplies',
        'archived' : 0,
        'author' : 'me',
        'receivedDate' : new Date(2012,3,19),
        'teaser' : 'Pens, pencils, calculator',
        'body' : 'Pens, pencils, calculator',
        'deleted' : 0,
        'priority' : 1,
        'read' : 0,
        'subTypes' : {
            'todo' : {
                'done' : 0,
                'notes' : ''
            },
            'due' : {
                'date' : day2
            }
        }    
    },
    
    8 : {
        'id' : 8,
        'type' : 'message',
        'title' : 'Math Placement Test Results',
        'archived' : 0,
        'author' : 'Jack Tuttle',
        'receivedDate' : new Date(2012,3,22),
        'teaser' : 'Your math placement test scores are in!  You scored: 44/100, which places you in the bottom 2% of incoming freshmen.  You will be placed in our pre-college math course, MATH 089 - Shapes and Counting.  Good luck!',
        'body' : 'Your math placement test scores are in!  You scored: 44/100, which places you in the bottom 2% of incoming freshmen.  You will be placed in our pre-college math course, MATH 089 - Shapes and Counting.  Good luck!',
        'deleted' : 0,
        'priority' : 0,
        'read' : 0,
        'subTypes' : { }    
    },
    9 : {
        'id' : 9,
        'type' : 'message',
        'title' : 'PHIL 101 introduction',
        'archived' : 0,
        'author' : 'Patrick Dewey',
        'receivedDate' : new Date(2012,3,24),
        'teaser' : 'Hello class!  I am Patrick Dewey, former American Gladiator and present Philosophy 101 professor.  I just wanted to make sure everyone brings Descartes\' Meditations on First Philosophy to class on Monday, August 27th.  After going over our syllabus, we will be breaking into groups to talk about what "is" is.',
        'body' : 'Hello class!  I am Patrick Dewey, former American Gladiator and present Philosophy 101 professor.  I just wanted to make sure everyone brings Descartes\' Meditations on First Philosophy to class on Monday, August 27th.  After going over our syllabus, we will be breaking into groups to talk about what "is" is.',
        'deleted' : 0,
        'priority' : 0,
        'read' : 0,
        'subTypes' : { }    
    },
    10 : {
        'id' : 10,
        'type' : 'message',
        'title' : 'See you in the dorms!',
        'archived' : 0,
        'author' : 'Sam Powers',
        'receivedDate' : new Date(2012,3,10),
        'teaser' : 'Hey roomie!  I can\'t wait until move-in day!  I just wanted to shoot you a message to make sure you\'re cool with me bringing my pog collection to the dorm.  I\'ve won 2 out of the past 7 national pog championships, so the collection is pretty important to me.  I have a 5\' x 3\' display case for my slammers that I\'ll be bringing as well.  Thanks friend!',
        'body' : 'Hey roomie!  I can\'t wait until move-in day!  I just wanted to shoot you a message to make sure you\'re cool with me bringing my pog collection to the dorm.  I\'ve won 2 out of the past 7 national pog championships, so the collection is pretty important to me.  I have a 5\' x 3\' display case for my slammers that I\'ll be bringing as well.  Thanks friend!',
        'deleted' : 0,
        'priority' : 0,
        'read' : 0,
        'subTypes' : { }    
    },
}