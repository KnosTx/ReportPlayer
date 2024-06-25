# ReportPlayer

ReportPlayer is a Minecraft plugin that facilitates reporting of player misconduct directly within the game environment. It provides players with a straightforward way to report rule violations, and administrators with tools to manage these reports effectively.

## Features

- **Player Reporting**: Allows players to report others for misconduct using an in-game form.
- **Admin Tools**: Provides administrators with commands to view, manage, and clear reported incidents.
- **Customizable**: Configurable exempt players list and customizable messages through the `playerreport.yml` configuration file.

## Installation

To install the ReportPlayer plugin, follow these steps:

1. Download the plugin `.phar` file from [Poggit releases](https://poggit.pmmp.io/p/ReportPlayer).
2. Place the downloaded `.phar` file into your server's `plugins` folder.
3. Start or reload your server. ReportPlayer should be loaded automatically.

## Usage

### Reporting a Player

Players can report another player using the `/report <player>` command:

```
/report <player>
```

This command opens a form where the reporting player can enter additional details about the incident.

### Managing Reports

Administrators can manage reports using the following commands:

- **View Pending Reports**: `/reportlist`
  - Lists all pending reports, showing details of each reported incident.

- **Clear Reports**: `/clearreports`
  - Clears all pending reports from the system.

### Configuration

The plugin uses the `playerreport.yml` file for configuration. Here are the configurable options:

```yaml
# playerreport.yml

# List of players who cannot be reported (e.g., admins or VIPs)
exemptPlayers:
  - Player1
  - Player2
  - Player3

# Messages sent to players and admins
messages:
  reportSuccess: "Your report has been successfully submitted."
  reportFail: "Unable to report this player at this time."
  reportListHeader: "Pending Reports:"
  noReports: "There are no pending reports at this time."
  noPermission: "You do not have permission to use this command."
  playerNotFound: "Player not found or not online."
  usageReport: "Usage: /report <player>"
  usageReportList: "Usage: /reportlist"
  allReportsCleared: "All reports have been cleared."

# Other settings
settings:
  saveReportsToFile: true
  reportFileName: "reports.json"
```

### Permissions

- `reportplayer.report`:
  - Allows players to report other players.
  - Default: All players.

- `reportplayer.viewlist`:
  - Allows players to view pending reports using `/reportlist`.
  - Default: Operators (OPs).

### Support

For issues or feature requests, please visit our [GitHub repository](https://github.com/NurAzliYT/ReportPlayer) and create an issue. We welcome community contributions and feedback!

### License

This plugin is licensed under the MIT License. See the [LICENSE](LICENSE) file for details.

---

Created by NurAzliYT
