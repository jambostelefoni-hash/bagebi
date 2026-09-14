import { createTheme, responsiveFontSizes, makeStyles } from '@material-ui/core/styles';

let theme = createTheme({
  palette: {
    primary: { main: '#3157d5', dark: '#233eaa' },
    secondary: { main: '#21b887' },
    background: { default: '#ffffff', paper: '#ffffff' },
    text: { primary: '#14213d', secondary: '#68748a' },
    error: { main: '#c64c4c' }
  },
  shape: { borderRadius: 12 },
  typography: {
    fontFamily: '"Noto Sans Georgian", "Segoe UI", sans-serif',
    h4: { fontWeight: 800 }, h5: { fontWeight: 800 }, button: { fontWeight: 700 }
  },
  overrides: {
    MuiInputLabel: { root: { color: '#68748a', fontSize: 13, '&$focused': { color: '#3157d5' } } },
    MuiInput: { root: { minHeight: 48, fontSize: 13 }, underline: { '&:before': { borderBottomColor: '#dfe5ef' }, '&:after': { borderBottomColor: '#3157d5' } } },
    MuiFormHelperText: { root: { fontSize: 10, marginTop: 6 } },
    MuiSelect: { select: { paddingTop: 16, paddingBottom: 10 } },
    MuiMenuItem: { root: { fontFamily: '"Noto Sans Georgian", sans-serif', fontSize: 12 } }
  }
});
theme = responsiveFontSizes(theme);

const useStyle = makeStyles(() => ({ page: { width: '100%', margin: 0, padding: 0 } }));
export { theme, useStyle };
