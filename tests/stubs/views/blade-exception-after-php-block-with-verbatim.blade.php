<h1>This is a blade view with a verbatim block inside a php block</h1>
@php
    /* @verbatim


    Only text
    @endverbatim */
@endphp
{{ somethingBadHappens() }}
<p>{{ 'One' }}</p>
<p>{{ 'Two' }}</p>
<p>{{ 'Three' }}</p>
