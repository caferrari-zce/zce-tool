$(document).ready(function(){


    var questionTemplate = $('.question_line:first').clone();

    // Ensure that the value of the template is empty
    questionTemplate.find('.answer_field').val('');

    var table = $('.answer_table');

    table.on('keyup', '.answer_field', function(e){
        table.find('.answer_field').each(function() {
            var tr = $(this).closest('tr');
            var last = tr.is(table.find('tbody tr:last'));

            if (!last && '' == $(this).val()) {
                $(this).closest('tr').remove();
            } else if (last && '' != $(this).val()) {
                var newLine = questionTemplate.clone();
                newLine.find('.btn').button();
                table.append(newLine);
            }

            if (last) {
                tr.find('.remove').hide();
            } else {
                tr.find('.remove').show();
            }

        });
    });

    table.on('click', '.remove', function() {
        var tr = $(this).closest('tr');
        var last = tr.is(table.find('tbody tr:last'));

        if (last) {
            return;
        }

        tr.remove();
    });

    $('#question_form .nav-tabs a').on('shown.bs.tab', function (e) {
      $('input[name=type]').val($(this).data('value'));
    })


    table.on('click', '.correct', function(e) {
        e.preventDefault();

        $(this).toggleClass('active');

        var span = $(this).find('span');
        var input = $(this).find('input');

        span.removeClass('glyphicon-thumbs-up glyphicon-thumbs-down');

        if ($(this).hasClass('active')) {
            span.addClass('glyphicon-thumbs-up');
            input.val('y');
        }else{
            span.addClass('glyphicon-thumbs-down');
            input.val('n');
        }

    });

    $('.add_code').on('click', function() {
        $(this).hide();
        $(this).closest('.form-group').find('textarea').show();
    });

    $('.btn_show_answer').on('click', function() {

        $(this).closest('h4').hide().next().show();

    });



});