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
            <td >
                <table >
                    <tr><td class="form-control-l2"></td></tr>
                </table>
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
            <td >
                <table>
                    <tr><td class="form-control-l2"></td></tr>
                </table>
            </td>
        @endfor
    </tr>
</table>
