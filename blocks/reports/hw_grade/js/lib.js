jQuery(document).ready(function () {
    jQuery(function () {
        jQuery(".user-name").tooltip({
            items: ".user-name",
            content: function () {
                var element = jQuery(this);
                if (element.is(".user-name")) {
                    var userGroups = element.attr("data-groups");
                    var userDesc = element.attr("data-description");
                    var html = "";

                    if (userGroups && userGroups.length > 0) {
                        html += '<div><p><strong>Группы:</strong></p>';
                        html += userGroups.split(';;').reduce(function (prev, current) {
                            return prev + '<p>' + current + '</p>';
                        });
                        html += '</div>';
                    }
                    if (userDesc && userDesc.length > 0) {
                        html += '<div><p><strong>Заметки:</strong></p>';
                        html += userDesc.split(';;').reduce(function (prev, current) {
                            return prev + '<p>' + current + '</p>';
                        });
                        html += '</div>';
                    }
                    return html;
                }
            }
        });
        jQuery(".course-name").tooltip({
            items: ".course-name",
            content: function() {
                var element = jQuery( this );
                if ( element.is(".course-name") ) {
                    var courseName = element.attr("data-coursename");
                    var html = "";

                    if (courseName) {
                        html += '<div><strong>Курс: </strong>' + courseName + '</div>';
                    }
                    return html;
                }
            }
        });
    });
});
