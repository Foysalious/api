<table>
    <tr>
        @for($i=1;$i<7;$i++)
            <td>
                Month {{$i}}
            </td>

        @endfor
    </tr>
    <tr>

        @for($i=1;$i<7;$i++)
            <td class="form-control-l">
            </td>
        @endfor
    </tr>
    <tr>
        @for($i=7;$i<13;$i++)
            <td>
                Month {{$i}}
            </td>

        @endfor
    </tr>
    <tr>

        @for($i=1;$i<7;$i++)
            <td class="form-control-l">
            </td>
        @endfor
    </tr>
</table>
