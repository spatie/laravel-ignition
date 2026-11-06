<h1>This is a blade view with a verbatim block</h1>

@verbatim
    <p>@php is only text here</p>
@endverbatim

<x-title
    title="A component"
/>

{{ somethingBadHappens() }}

@php
    $value = 1;
@endphp
