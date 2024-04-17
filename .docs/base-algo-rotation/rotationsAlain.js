function generateRotations(teams, stands, battleStands) {
  let rotations = [];
  let teamLocations = new Map(teams.map((team) => [team, []]));
  let standOccupancy = new Map(stands.map((stand) => [stand, []]));

  for (let round = 0; round < stands.length; round++) {
    let currentRound = {};
    let availableTeams = new Set(teams);
    let availableStands = new Set(stands);

    // Assign battle stands first
    for (let stand of battleStands) { 
      if (availableStands.has(stand)) {
        const teamsAtStand = [];
        for (let i = 0; i < 2; i++) {
          let team = selectTeamForStand(
            availableTeams,
            teamLocations,
            stand,
            standOccupancy
          );
          if (team) {
            teamsAtStand.push(team);
            availableTeams.delete(team);
            teamLocations.get(team).push(stand);
          }
        }
        if (teamsAtStand.length > 0) {
          currentRound[stand] = teamsAtStand;
          availableStands.delete(stand);
        }
      }
    }

    // Assign regular stands
    for (let stand of availableStands) {
      let team = selectTeamForStand(
        availableTeams,
        teamLocations,
        stand,
        standOccupancy
      );
      if (team) {
        currentRound[stand] = [team];
        teamLocations.get(team).push(stand);
        availableTeams.delete(team);
      }
    }

    rotations.push(currentRound);
  }

  return rotations;
}

function selectTeamForStand(
  availableTeams,
  teamLocations,
  stand,
  standOccupancy
) {
  return Array.from(availableTeams).find(
    (team) =>
      !teamLocations.get(team).includes(stand) &&
      !(standOccupancy.get(stand).at(-1) === team)
  );
}

// Example usage
let teams = ["Team A", "Team B", "Team C", "Team D"];
let stands = ["Stand 1", "Stand 2", "Stand 3", "Stand 4"];
let battleStands = ["Stand 1"];

let rotations = generateRotations(teams, stands, battleStands);
console.log(JSON.stringify(rotations, null, 2));
