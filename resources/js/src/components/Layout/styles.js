import {
  createTheme,
  responsiveFontSizes,
  makeStyles
} from '@material-ui/core/styles';
let theme = createTheme({
  palette: {
    primary: {
      main: '#1f3a8a'
    },
    secondary: {
      main: '#0f766e'
    },
    background: {
      default: '#f2f5f9',
      paper: '#ffffff'
    },
    text: {
      primary: '#101828',
      secondary: '#5d6b7a'
    }
  },
  typography: {
    fontFamily: '"Manrope", "Segoe UI", Arial, sans-serif',
    h4: {
      fontFamily: '"Sora", "Manrope", "Segoe UI", Arial, sans-serif',
      fontWeight: 700
    },
    h5: {
      fontFamily: '"Sora", "Manrope", "Segoe UI", Arial, sans-serif',
      fontWeight: 600
    }
  }
});

theme = responsiveFontSizes(theme);

const useStyle = makeStyles(() => ({
  page: {
    width: '100%',
    maxWidth: '100%',
    margin: '0 auto 24px',
    padding: theme.spacing(0, 2),
    [theme.breakpoints.down('xs')]: {
      maxWidth: '100%',
      marginBottom: theme.spacing(2),
      padding: 0
    }
  },
  hero: {
    background: 'linear-gradient(135deg, rgba(31, 58, 138, 0.95), rgba(15, 118, 110, 0.92))',
    color: '#ffffff',
    borderRadius: 18,
    padding: theme.spacing(3, 3.5),
    display: 'flex',
    alignItems: 'center',
    justifyContent: 'space-between',
    gap: theme.spacing(2),
    boxShadow: '0 16px 32px rgba(15, 23, 42, 0.18)',
    marginBottom: theme.spacing(3)
  },
  heroContent: {
    maxWidth: 560
  },
  heroKicker: {
    fontSize: 12,
    letterSpacing: 1.6,
    textTransform: 'uppercase',
    margin: 0,
    color: 'rgba(255, 255, 255, 0.75)'
  },
  heroTitle: {
    color: '#ffffff',
    margin: theme.spacing(1, 0, 1)
  },
  heroSubtitle: {
    color: 'rgba(255, 255, 255, 0.82)'
  },
  heroEmblem: {
    borderRadius: 12,
    border: '1px solid rgba(255, 255, 255, 0.35)',
    background: 'rgba(255, 255, 255, 0.1)',
    padding: theme.spacing(1),
    display: 'flex',
    alignItems: 'center',
    justifyContent: 'center'
  },
  paper: {
    padding: theme.spacing(3),
    borderRadius: 16,
    border: '1px solid #d5dde8',
    boxShadow: '0 12px 24px rgba(15, 23, 42, 0.12)',
    width: '100%',
    [theme.breakpoints.down('xs')]: {
      borderRadius: 0,
      padding: theme.spacing(2),
      borderLeft: 0,
      borderRight: 0
    }
  },
  sectionTitle: {
    marginBottom: theme.spacing(1)
  },
  sectionSubtitle: {
    marginBottom: theme.spacing(2),
    color: theme.palette.text.secondary
  },
  notice: {
    marginBottom: theme.spacing(2)
  },
  searchWrap: {
    marginBottom: theme.spacing(2)
  }
}));

export { theme, useStyle };

