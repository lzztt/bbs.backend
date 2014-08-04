1. Responsive design for all element
2. progressive enhancement
3. responsive table using JavaScript, use
4. http://diveintohtml5.info/forms.html
5. http://www.html5rocks.com/en/mobile/responsivedesign/

http://blog.apps.npr.org/2014/05/09/responsive-data-tables.html
/* responsive table */
@media screen and (max-width: 480px) {
    table,
    tbody {
        display: block;
        width: 100%:
    }
Make the table display: block; instead of display: table; and make sure it spans the full width of the content well.

    thead { display: none; }
Hide the header row.

    table tr,
    table th,
    table td {
        display: block;
        padding: 0;
        text-align: left;
        white-space: normal;
    }
Make all the <tr>, <th> and <td> tags display as rows rather than columns. (<th> is probably not necessary to include, since we're hiding the <thead>, but I'm doing so for completeness.)

    table tr {
        border-bottom: 1px solid #eee;
        padding-bottom: 11px;
        margin-bottom: 11px;
    }
Add a dividing line between each row of data.

    table th[data-title]:before,
    table td[data-title]:before {
        content: attr(data-title) ":\00A0";
        font-weight: bold;
    }

If a table cell has a data-table attribute, prepend it to the contents of the table cell. (e.g., <td data-title="January">6.5</td> would display as January: 6.5)

    table td {
        border: none;
        margin-bottom: 6px;
        color: #444;
    }
Table cell style refinements.

    table td:empty { display: none; }
Hide empty table cells.